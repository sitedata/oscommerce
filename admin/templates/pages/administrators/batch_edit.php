<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License
*/

  $osC_DirectoryListing = new osC_DirectoryListing('includes/modules/access');
  $osC_DirectoryListing->setIncludeDirectories(false);

  $access_modules_array = array();

  foreach ($osC_DirectoryListing->getFiles() as $file) {
    $module = substr($file['name'], 0, strrpos($file['name'], '.'));

    if (!class_exists('osC_Access_' . ucfirst($module))) {
      $osC_Language->loadConstants('modules/access/' . $file['name']);
      include($osC_DirectoryListing->getDirectory() . '/' . $file['name']);
    }

    $module = 'osC_Access_' . ucfirst($module);
    $module = new $module();

    $access_modules_array[osC_Access::getGroupTitle( $module->getGroup() )][] = array('id' => $module->getModule(),
                                                                                      'text' => $module->getTitle());
  }

  ksort($access_modules_array);
?>

<h1><?php echo osc_link_object(osc_href_link(FILENAME_DEFAULT, $osC_Template->getModule()), $osC_Template->getPageTitle()); ?></h1>

<?php
  if ($osC_MessageStack->size($osC_Template->getModule()) > 0) {
    echo $osC_MessageStack->output($osC_Template->getModule());
  }
?>

<div class="infoBoxHeading"><?php echo osc_icon('configure.png', IMAGE_EDIT) . ' Batch Edit'; ?></div>
<div class="infoBoxContent">
  <form name="aEditBatch" action="<?php echo osc_href_link(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page'] . '&action=batchSave'); ?>" method="post">

  <p><?php echo TEXT_EDIT_BATCH_INTRO; ?></p>

<?php
  $Qadmins = $osC_Database->query('select id, user_name from :table_administrators where id in (":id") order by user_name');
  $Qadmins->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
  $Qadmins->bindRaw(':id', implode('", "', array_unique(array_filter(array_slice($_POST['batch'], 0, MAX_DISPLAY_SEARCH_RESULTS), 'is_numeric'))));
  $Qadmins->execute();

  $names_string = '';

  while ($Qadmins->next()) {
    $names_string .= osc_draw_hidden_field('batch[]', $Qadmins->valueInt('id')) . '<b>' . $Qadmins->value('user_name') . '</b>, ';
  }

  if ( !empty($names_string) ) {
    $names_string = substr($names_string, 0, -2) . osc_draw_hidden_field('subaction', 'confirm');
  }

  echo '<p>' . $names_string . '</p>';

  echo '<p>' . osc_draw_radio_field('type', array(array('id' => 'add', 'text' => 'Add To'), array('id' => 'remove', 'text' => 'Remove From'), array('id' => 'set', 'text' => 'Set To')), 'add') . '</p>';

  echo '<ul style="list-style-type: none; padding-left: 0;">' .
       '  <li>' . osc_draw_checkbox_field('modules[]', '*', null, 'id="access_globaladmin"') . '&nbsp;<label for="access_globaladmin"><b>' . TEXT_GLOBAL_ACCESS . '</b></label></li>' .
       '</ul>' .
       '<ul style="list-style-type: none; padding-left: 0;">';

  foreach ( $access_modules_array as $group => $modules ) {
    echo '  <li><b>' . $group . '</b>' .
         '    <ul style="list-style-type: none; padding-left: 15px;">';

    foreach ($modules as $module) {
      echo '      <li>' . osc_draw_checkbox_field('modules[]', $module['id'], null, 'id="access_' . $module['id'] . '"') . '&nbsp;<label for="access_' . $module['id'] . '" class="fieldLabel">' . $module['text'] . '</label></li>';
    }

    echo '    </ul>' .
         '  </li>';
  }

  echo '</ul>';
?>

  <p align="center"><?php echo '<input type="submit" value="' . IMAGE_SAVE . '" class="operationButton"> <input type="button" value="' . IMAGE_CANCEL . '" onclick="document.location.href=\'' . osc_href_link_admin(FILENAME_DEFAULT, $osC_Template->getModule() . '&page=' . $_GET['page']) . '\';" class="operationButton">'; ?></p>

  </form>
</div>