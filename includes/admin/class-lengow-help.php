<?php
/**
 * Installation related functions and actions.
 *
 * @author   Lengow
 * @category Admin
 * @package  Lengow/Classes
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Lengow_Help Class.
 */
class Lengow_Help {

    /**
     * Display help page
     */
    public static function display() {

        $locale = new Lengow_Translation();
        $mail_to = self::get_mail_to();
        $keys   = Lengow_Configuration::get_keys();
        include_once 'views/help/html-admin-help.php';

    }

    /**
     * Generate mailto for help page
     */
    public static function get_mail_to()
    {
        $locale = new Lengow_Translation();

        //TODO - get sync data function
        $mail_to = Lengow_Sync::get_sync_data();

        $mail = 'support@supportlengow.zendesk.com';
        $subject = $locale->t('help.screen.mailto_subject');

        //TODO - query api function
        $result = Lengow_Connector::query_api();

        $body = '%0D%0A%0D%0A%0D%0A%0D%0A%0D%0A'
            . $locale->t('help.screen.mail_lengow_support_title').'%0D%0A';
        if (isset($result->cms)) {
            $body .= 'commun_account : '.$result->cms->common_account.'%0D%0A';
        }

        //TODO - get sync data on mail body
//
//        foreach ($mail_to as $key => $value) {
//            if ($key == 'domain_name' || $key == 'token' || $key == 'return_url' || $key == 'shops') {
//                continue;
//            }
//            $body .= $key.' : '.$value.'%0D%0A';
//        }
//        $shops = $mail_to['shops'];
//        $i = 1;
//        foreach ($shops as $shop) {
//            foreach ($shop as $item => $value) {
//                if ($item == 'name') {
//                    $body .= 'Store '.$i.' : '.$value.'%0D%0A';
//                } elseif ($item == 'feed_url') {
//                    $body .= $value . '%0D%0A';
//                }
//            }
//            $i++;
//        }

        $html = ' <a href="mailto:'. $mail;
        $html.= '?subject='. $subject;
        $html.= '&body='. $body .'" ';
        $html.= 'title="'. $locale->t('help.screen.need_some_help').'" target="_blank">';
        $html.=  $locale->t('help.screen.mail_lengow_support');
        $html.= '</a>';
        return $html;
    }
}