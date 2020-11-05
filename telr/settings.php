<?php
/**
 * iSpace Technology | Telr enrolment plugin.
 * info@ispce.net   *** +201149444844
 * This plugin allows you to set up paid courses with Telr Gateway.
 * @package    enrol_telr
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    //--- settings ------------------------------------------------------------------------------------------
    //$settings->add(new admin_setting_heading('enrol_telr_settings'));

    // Note: let's reuse the ext sync constants and strings here, internally it is very similar,
    //       it describes what should happen when users are not supposed to be enrolled any more.
    $options = array(
        ENROL_EXT_REMOVED_KEEP => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configtext('enrol_telr/telr_ivp_store', get_string('ivp_store', 'enrol_telr'), null, null));
    $settings->add(new admin_setting_configtext('enrol_telr/telr_ivp_authkey', get_string('ivp_authkey', 'enrol_telr'), null, null));
    $settings->add(new admin_setting_configselect('enrol_telr/expiredaction', get_string('expiredaction', 'enrol_telr'), get_string('expiredaction_help', 'enrol_telr'), ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));

    //--- enrol instance defaults ----------------------------------------------------------------------------
    $settings->add(new admin_setting_heading(
        'enrol_telr_defaults',
        get_string('enrolinstancedefaults', 'admin'),
        get_string('enrolinstancedefaults_desc', 'admin')
    ));

    $options = array(ENROL_INSTANCE_ENABLED => get_string('yes'),
        ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect(
        'enrol_telr/status',
        get_string('status', 'enrol_telr'),
        get_string('status_desc', 'enrol_telr'),
        ENROL_INSTANCE_DISABLED,
        $options
    ));

    $settings->add(new admin_setting_configtext('enrol_telr/cost', get_string('cost', 'enrol_telr'), '', 0, PARAM_FLOAT, 4));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect(
            'enrol_telr/roleid',
            get_string('defaultrole', 'enrol_telr'),
            get_string('defaultrole_desc', 'enrol_telr'),
            $student->id,
            $options
        ));
    }

    $settings->add(new admin_setting_configduration(
        'enrol_telr/enrolperiod',
        get_string('enrolperiod', 'enrol_telr'),
        get_string('enrolperiod_desc', 'enrol_telr'),
        0
    ));
}
