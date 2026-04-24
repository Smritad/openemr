<?php
/* Smarty version 4.5.6, created on 2026-04-03 09:23:33
  from 'C:\xampp\htdocs\open_cms\templates\insurance_numbers\general_list.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_69cf6af5cbc6a9_23730975',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'ba3051a580fc4e61a72641d00142bcff04b1ed02' => 
    array (
      0 => 'C:\\xampp\\htdocs\\open_cms\\templates\\insurance_numbers\\general_list.html',
      1 => 1775132415,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_69cf6af5cbc6a9_23730975 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'C:\\xampp\\htdocs\\open_cms\\library\\smarty\\plugins\\function.xlt.php','function'=>'smarty_function_xlt',),));
?>
<div class="table-responsive">
  <table class="table table-striped">
      <thead>
      <tr>
          <th><?php echo smarty_function_xlt(array('t'=>'Name'),$_smarty_tpl);?>
</th>
          <th>&nbsp;</th>
          <th><?php echo smarty_function_xlt(array('t'=>'Provider'),$_smarty_tpl);?>
 #</th>
          <th><?php echo smarty_function_xlt(array('t'=>'Rendering'),$_smarty_tpl);?>
 #</th>
          <th><?php echo smarty_function_xlt(array('t'=>'Group'),$_smarty_tpl);?>
 #</th>
      </tr>
      </thead>
      <tbody>
      <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['providers']->value, 'provider');
$_smarty_tpl->tpl_vars['provider']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['provider']->value) {
$_smarty_tpl->tpl_vars['provider']->do_else = false;
?>
      <tr>
          <td>
              <a href="<?php echo $_smarty_tpl->tpl_vars['CURRENT_ACTION']->value;?>
action=edit&id=default&provider_id=<?php echo attr_url($_smarty_tpl->tpl_vars['provider']->value->id);?>
" onclick="top.restoreSession()">
                  <?php echo text($_smarty_tpl->tpl_vars['provider']->value->get_name_display());?>

              </a>
          </td>
          <td><?php echo smarty_function_xlt(array('t'=>'Default'),$_smarty_tpl);?>
&nbsp;</td>
          <td><?php echo text($_smarty_tpl->tpl_vars['provider']->value->get_provider_number_default());?>
&nbsp;</td>
          <td><?php echo text($_smarty_tpl->tpl_vars['provider']->value->get_rendering_provider_number_default());?>
&nbsp;</td>
          <td><?php echo text($_smarty_tpl->tpl_vars['provider']->value->get_group_number_default());?>
&nbsp;</td>
      </tr>
      <?php
}
if ($_smarty_tpl->tpl_vars['provider']->do_else) {
?>
      <tr>
          <td><?php echo smarty_function_xlt(array('t'=>'No Providers Found'),$_smarty_tpl);?>
</td>
      </tr>
      <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
      </tbody>
  </table>
</div>
<?php }
}
