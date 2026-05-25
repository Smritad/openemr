<?php
/**
 * Minimal Markdown → printable HTML converter for OpenCMS tab docs.
 *
 * Usage: php docs/_md_to_html.php <input.md> <output.html>
 *
 * Handles: ATX headers, fenced code blocks, GFM tables, ordered/unordered
 * lists, blockquotes, horizontal rules, inline code, bold, italic, links,
 * and ASCII art preservation inside code fences.
 */

if ($argc < 3) {
    fwrite(STDERR, "Usage: php _md_to_html.php input.md output.html\n");
    exit(1);
}

$inputPath  = $argv[1];
$outputPath = $argv[2];

if (!is_file($inputPath)) {
    fwrite(STDERR, "Input not found: $inputPath\n");
    exit(1);
}

$md = file_get_contents($inputPath);
$md = str_replace(["\r\n", "\r"], "\n", $md);

/* ── 1. Pull out fenced code blocks and tables first so inline rules don't
 *      mangle them. We replace them with placeholder tokens, then put the
 *      rendered HTML back at the end. */
$placeholders = [];
$tokenIndex = 0;

function reserve(string $html, array &$store, int &$idx): string
{
    $token = "@@@PH" . $idx++ . "@@@";
    $store[$token] = $html;
    return $token;
}

// Fenced code blocks ``` ... ```
$md = preg_replace_callback('/```(\w*)\n(.*?)\n```/s', function ($m) use (&$placeholders, &$tokenIndex) {
    $body = htmlspecialchars($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $html = '<pre><code>' . $body . '</code></pre>';
    return reserve($html, $placeholders, $tokenIndex);
}, $md);

// GFM tables — must have a header separator row like | --- | --- |
$md = preg_replace_callback(
    '/(^\|.+\|\n\|[\s:|-]+\|\n(?:\|.+\|\n?)+)/m',
    function ($m) use (&$placeholders, &$tokenIndex) {
        $lines = array_values(array_filter(explode("\n", trim($m[1]))));
        if (count($lines) < 2) {
            return $m[1];
        }
        $headerCells = array_map('trim', explode('|', trim($lines[0], '|')));
        $bodyRows = array_slice($lines, 2);

        $html = '<table>';
        $html .= '<thead><tr>';
        foreach ($headerCells as $c) {
            $html .= '<th>' . inline_render($c) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($bodyRows as $row) {
            $cells = array_map('trim', explode('|', trim($row, '|')));
            $html .= '<tr>';
            foreach ($cells as $c) {
                $html .= '<td>' . inline_render($c) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return reserve($html, $placeholders, $tokenIndex) . "\n";
    },
    $md
);

/* ── 2. Inline rendering — applied to non-code, non-table text. */
function inline_render(string $text): string
{
    // Inline code first to protect from other rules
    $text = preg_replace_callback('/`([^`\n]+)`/', function ($m) {
        return '<code>' . htmlspecialchars($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</code>';
    }, $text);

    // Bold then italic (order matters: ** before *)
    $text = preg_replace('/\*\*([^*\n]+)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/(?<![*\w])\*([^*\n]+)\*(?!\w)/', '<em>$1</em>', $text);

    // Markdown links [text](url)
    $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($m) {
        $url = htmlspecialchars($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return '<a href="' . $url . '">' . $m[1] . '</a>';
    }, $text);

    return $text;
}

/* ── 3. Block-level rendering, line by line. */
$lines = explode("\n", $md);
$out = [];
$inList = null;       // 'ul' | 'ol' | null
$inBlockquote = false;
$paragraph = [];

$flushParagraph = function () use (&$paragraph, &$out) {
    if (!empty($paragraph)) {
        $text = trim(implode(' ', $paragraph));
        if ($text !== '') {
            $out[] = '<p>' . inline_render($text) . '</p>';
        }
        $paragraph = [];
    }
};

$closeList = function () use (&$inList, &$out) {
    if ($inList !== null) {
        $out[] = "</{$inList}>";
        $inList = null;
    }
};

$closeBlockquote = function () use (&$inBlockquote, &$out) {
    if ($inBlockquote) {
        $out[] = '</blockquote>';
        $inBlockquote = false;
    }
};

foreach ($lines as $line) {
    // Placeholder lines (code blocks / tables) pass through untouched
    if (preg_match('/^@@@PH\d+@@@$/', trim($line))) {
        $flushParagraph();
        $closeList();
        $closeBlockquote();
        $out[] = trim($line);
        continue;
    }

    // Horizontal rule
    if (preg_match('/^---+\s*$/', $line)) {
        $flushParagraph();
        $closeList();
        $closeBlockquote();
        $out[] = '<hr>';
        continue;
    }

    // Headers
    if (preg_match('/^(#{1,6})\s+(.*)$/', $line, $m)) {
        $flushParagraph();
        $closeList();
        $closeBlockquote();
        $level = strlen($m[1]);
        $out[] = "<h{$level}>" . inline_render(trim($m[2])) . "</h{$level}>";
        continue;
    }

    // Blockquotes
    if (preg_match('/^>\s?(.*)$/', $line, $m)) {
        $flushParagraph();
        $closeList();
        if (!$inBlockquote) {
            $out[] = '<blockquote>';
            $inBlockquote = true;
        }
        $out[] = '<p>' . inline_render($m[1]) . '</p>';
        continue;
    }
    $closeBlockquote();

    // Unordered list
    if (preg_match('/^[-*]\s+(.*)$/', $line, $m)) {
        $flushParagraph();
        if ($inList !== 'ul') {
            $closeList();
            $out[] = '<ul>';
            $inList = 'ul';
        }
        $out[] = '<li>' . inline_render($m[1]) . '</li>';
        continue;
    }

    // Ordered list
    if (preg_match('/^\d+\.\s+(.*)$/', $line, $m)) {
        $flushParagraph();
        if ($inList !== 'ol') {
            $closeList();
            $out[] = '<ol>';
            $inList = 'ol';
        }
        $out[] = '<li>' . inline_render($m[1]) . '</li>';
        continue;
    }

    // Blank line → flush paragraph / close list
    if (trim($line) === '') {
        $flushParagraph();
        $closeList();
        continue;
    }

    // Otherwise: paragraph text
    $paragraph[] = $line;
}

$flushParagraph();
$closeList();
$closeBlockquote();

$body = implode("\n", $out);

// Reinsert placeholders
$body = strtr($body, $placeholders);

/* ── 4. Wrap in printable HTML with brand-aware print CSS. */
$title = basename($inputPath, '.md');

$html = <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{$title}</title>
<style>
  @page { size: A4; margin: 18mm 16mm; }
  :root {
    --brand-red: #cc0000;
    --brand-navy: #1a1a2e;
    --ink: #1f2328;
    --muted: #57606a;
    --border: #d0d7de;
    --bg-subtle: #f6f8fa;
  }
  * { box-sizing: border-box; }
  body {
    font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: var(--ink);
    line-height: 1.55;
    font-size: 11pt;
    margin: 0;
  }
  h1, h2, h3, h4 { color: var(--brand-navy); page-break-after: avoid; }
  h1 { font-size: 22pt; border-bottom: 3px solid var(--brand-red); padding-bottom: 6px; margin-top: 0; }
  h2 { font-size: 16pt; border-bottom: 1px solid var(--border); padding-bottom: 4px; margin-top: 22px; }
  h3 { font-size: 13pt; margin-top: 18px; }
  h4 { font-size: 11.5pt; color: var(--brand-red); }
  p  { margin: 8px 0; }
  hr { border: 0; border-top: 1px solid var(--border); margin: 18px 0; }
  a  { color: var(--brand-red); text-decoration: none; border-bottom: 1px dotted var(--brand-red); }
  blockquote {
    border-left: 4px solid var(--brand-red);
    background: var(--bg-subtle);
    margin: 12px 0;
    padding: 8px 14px;
    color: var(--muted);
    font-style: italic;
  }
  blockquote p { margin: 4px 0; }
  code {
    font-family: "Consolas", "SFMono-Regular", Menlo, monospace;
    background: var(--bg-subtle);
    padding: 1px 5px;
    border-radius: 3px;
    font-size: 0.92em;
  }
  pre {
    background: #0b1020;
    color: #e8eaf2;
    padding: 12px 14px;
    border-radius: 6px;
    font-size: 9.5pt;
    line-height: 1.4;
    overflow-x: auto;
    page-break-inside: avoid;
  }
  pre code { background: transparent; color: inherit; padding: 0; }
  table {
    width: 100%;
    border-collapse: collapse;
    margin: 12px 0;
    font-size: 10pt;
    page-break-inside: avoid;
  }
  th, td {
    border: 1px solid var(--border);
    padding: 6px 9px;
    text-align: left;
    vertical-align: top;
  }
  thead th {
    background: var(--brand-navy);
    color: #fff;
    font-weight: 600;
  }
  tbody tr:nth-child(even) { background: var(--bg-subtle); }
  ul, ol { padding-left: 22px; }
  li { margin: 3px 0; }
  strong { color: var(--brand-navy); }
  .footer-note { color: var(--muted); font-size: 9pt; }
</style>
</head>
<body>
{$body}
</body>
</html>
HTML;

if (file_put_contents($outputPath, $html) === false) {
    fwrite(STDERR, "Failed to write $outputPath\n");
    exit(1);
}

echo "Rendered: $outputPath\n";
