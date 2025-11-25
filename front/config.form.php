<?php

/**
 * -------------------------------------------------------------------------
 * Kanban Looks Good plugin for GLPI
 * -------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

Session::checkRight('config', UPDATE);

if (isset($_POST['update_config'])) {
    $result = PluginKanbanlooksgoodConfig::saveConfig($_POST);
    if ($result) {
        Session::addMessageAfterRedirect(__('Configuration saved successfully', 'kanbanlooksgood'), false, INFO);
    } else {
        Session::addMessageAfterRedirect(__('Error saving configuration', 'kanbanlooksgood'), false, ERROR);
    }
    Html::back();
} else {
    Html::header(
        __('Kanban Looks Good', 'kanbanlooksgood'),
        $_SERVER['PHP_SELF'],
        "config",
        "plugins"
    );

    PluginKanbanlooksgoodConfig::showConfigForm();

    Html::footer();
}
