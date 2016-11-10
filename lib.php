<?php

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Adds the 'Charon settings' link to the admin menu while on course page.
 *
 * @param navigation_node $parentnode the parent node ( > Course administration )
 * @param stdClass $course course info
 * @param context_course $context context
 */
function charon_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context)
{
    if (has_capability('mod/charon:addinstance', $context)) {
        $url = new moodle_url('/mod/charon/course_settings.php', array('id'=>$course->id));
        $addedNode = $parentnode->add(
            get_string('plugin_settings', 'charon'), $url, navigation_node::TYPE_SETTING, null, null,
            new pix_icon('i/settings', '')
        );

        // To add a child node:
//        $addedNode->add(
//            get_string('pluginname', 'charon'),
//            $url,
//            navigation_node::TYPE_SETTING,
//            null, null,
//            new pix_icon('i/settings', '')
//        );
    }
}
