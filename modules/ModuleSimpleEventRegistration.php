<?php

/**
 * TYPOlight webCMS
 *
 * The TYPOlight webCMS is an accessible web content management system that
 * specializes in accessibility and generates W3C-compliant HTML code. It
 * provides a wide range of functionality to develop professional websites
 * including a built-in search engine, form generator, file and user manager,
 * CSS engine, multi-language support and many more. For more information and
 * additional TYPOlight applications like the TYPOlight MVC Framework please
 * visit the project website http://www.typolight.org.
 *
 * PHP version 5
 * @copyright  2010 Felix Pfeiffer : Neue Medien
 * @author     Felix Pfeiffer
 * @package    simple_event_registration
 * @filesource
 */

namespace FelixPfeiffer\SimpleEventRegistration;

/**
 * Class ModuleSimpleEventRegistration
 *
 * Front end module "ModuleSimpleEventRegistration".
 * @copyright  2010 Felix Pfeiffer : Neue Medien
 * @author     Felix Pfeiffer
 * @package    simple_event_registration
 */
class ModuleSimpleEventRegistration extends \ModuleEventReader
{
    /**
     * Variable zum Testen, ob die Anmeldung erwünscht ist oder nicht.
     **/
    protected $blnParseRegistration = true;
    protected $blnShowList = true;
    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### SIMPLE EVENT REGISTRATION READER ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate module
     */
    protected function compile()
    {

        parent::compile();

        // Get current event
        $objEvent = \CalendarEventsModel::findPublishedByParentAndIdOrAlias(\Input::get('events'), $this->cal_calendar);

        // If current event isn't a registration event, don't go on
        if (!$objEvent->ser_register) {
            $this->blnParseRegistration = false;
            return false;
        }
        if (!$objEvent->ser_show) {
            $this->blnShowList = false;
        }

        // If current event isn't a registration event, don't go on
        if (!$objEvent->ser_show) {
            $this->blnShowList = false;
        }


        $arrMessage = array();

        // If everything is OK, show the form and perform the registration
        $this->Template->event .= $this->parseRegistration($objEvent);

        // If everything is OK, show the list of all registered members
        if ($this->blnShowList) {
            $this->Template->event .= $this->parseList($objEvent);
        }
    }

    protected function parseList($objEvent)
    {
        $objTemplate = new \FrontendTemplate('simple_events_registration_list');
        $objTemplate->blnShowList = true;

        $objTemplate->listHeadline = $objEvent->ser_showheadline;
        $objTemplate->listid = 'simple_event_registration_list_table';
        $objTemplate->listsummary = sprintf($GLOBALS['TL_LANG']['MSC']['ser_listsummary'], html_entity_decode($objEvent->title));

        $objRegistrations = $this->Database->prepare("SELECT * FROM tl_event_registrations WHERE pid=?")->execute($objEvent->id);

        if ($objRegistrations->numRows < 1) {
            $objTemplate->blnShowList = false;
            $objTemplate->listMessage = sprintf($GLOBALS['TL_LANG']['MSC']['ser_emptylist'], html_entity_decode($objEvent->title));
        } else {
            $arrRegistrations = array();
            $arrAnonym = array();
            $i = 0;
            while ($objRegistrations->next()) {
                $arrReg = array();



                $arrReg['firstname'] = '';
                $arrReg['lastname'] = '';
                $arrReg['email'] = '';
                $arrReg['id'] = '';

                $key = $arrReg['lastname'];
                $z=0;
                do {
                    $key = $arrReg['lastname'] . ++$z;
                } while (array_key_exists($key, $arrRegistrations));

                $arrRegistrations[$key] = $arrReg;



                if ($objRegistrations->anonym == 1 && $objRegistrations->lastname != '') {
                    $arrReg['firstname'] = $objRegistrations->firstname;
                    $arrReg['lastname'] = $objRegistrations->lastname;
                    $arrReg['email'] = $objRegistrations->email;
                    $arrReg['id'] = false;

                    $key = $arrReg['lastname'];
                    $z=0;
                    do {
                        $key = $arrReg['lastname'] . ++$z;
                    } while (array_key_exists($key, $arrRegistrations));

                    $arrRegistrations[$key] = $arrReg;
                }

                if ($objRegistrations->anonym == 1 && $objRegistrations->lastname == '') {
                    $arrReg['firstname'] = false;
                    $arrReg['lastname'] = 'Anonyme Anmeldung Nr.' . ++$i;
                    $arrReg['email'] = false;
                    $arrReg['id'] = false;

                    $arrAnonym[$arrReg['lastname']] = $arrReg;
                }
            }

            ksort($arrRegistrations);
            $arrRegistrations = array_merge($arrRegistrations, $arrAnonym);
            $j=0;
            $count = count($arrRegistrations);
            foreach ($arrRegistrations as $k => $v) {
                $class = ($j++==0 ? 'first ' : '') . ($j%2==0 ? 'even ' : 'odd ') . ($j == $count ? 'last' : '');
                $arrRegistrations[$k]['class'] = $class;
            }

            $objTemplate->head = $GLOBALS['TL_LANG']['MSC']['ser_list_heads'];
            $objTemplate->list = $arrRegistrations;
        }

        return $objTemplate->parse();
    }

    protected function parseRegistration($objEvent)
    {
        $objTemplate = new \FrontendTemplate('simple_events_registration_form');
        $objTemplate->blnShowForm = true;
        // ReCaptcha
        $GLOBALS['TL_HEAD'][] = "<script src='https://www.google.com/recaptcha/api.js?hl=de'></script>";

        $objTemplate->sitekey = $GLOBALS['TL_CONFIG']['ERMSiteKey'];
        $objTemplate->secretkey = $GLOBALS['TL_CONFIG']['ERMSecretKey'];


        $isregistered = false;


        //print_r($objEvent);

        // Anmeldefrist Checken
        if ($objEvent->ser_date < time()) {
            $objTemplate->blnShowForm = false;
            $arrMess['message'] = sprintf($GLOBALS['TL_LANG']['MSC']['ser_regclosed'], $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objEvent->ser_date));
            $arrMess['message_class'] = " closed";
            $blnEnded = true;
            $arrMessage[] = $arrMess;
        }

        // Is the user allready registered?
        if ($this->checkRegistration($this->User->id, $objEvent->id) && !$_SESSION['TL_SER_REGISTERED']) {
            $objTemplate->blnShowForm = false;
            $objTemplate->blnShowDiscardForm = true;
            $isregistered = true;
            $arrMess['message'] = $GLOBALS['TL_LANG']['MSC']['ser_regallready'];
            $arrMess['message_class'] = " allready";
            $arrMessage[] = $arrMess;
        }

        // Perform Registration
        if ($this->Input->post('FORM_SUBMIT') == 'tl_simple_event_registration' && $this->Input->post('register')) {
            $this->registerUser($objEvent);
        }

        // Perform Un-Registration
        if ($this->Input->post('FORM_SUBMIT') == 'tl_simple_event_cancelation' && $this->Input->post('unregister')) {
            $this->unregisterUser($objEvent);
        }

        // Are there still places left?
        $intPlaces = $this->checkPlaces($objEvent->id, $objEvent->ser_places);
        if (!$intPlaces && !$blnEnded) {
            $objTemplate->blnShowForm = false;
            $objTemplate->places = $isregistered ? $GLOBALS['TL_LANG']['MSC']['ser_full_reg'] : $GLOBALS['TL_LANG']['MSC']['ser_full'];
            $objTemplate->places_class = " full";
        } elseif ($blnEnded) {
        } else {
            $objTemplate->places = sprintf($GLOBALS['TL_LANG']['MSC']['ser_av_places'], $intPlaces);
            $objTemplate->quantity = $intPlaces;
            $objTemplate->places_class = "";
        }

        $objTemplate->ser_quantity = $this->ser_quantity;
        $objTemplate->quantity_label = $GLOBALS['TL_LANG']['MSC']['quantity_label'];


        // Confirmation message
        if ($_SESSION['TL_SER_REGISTERED']) {
            global $objPage;

            // Do not index the page
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            $objTemplate->blnShowForm = false;
            $arrMess['message'] = $GLOBALS['TL_LANG']['MSC']['ser_register_success'];
            $arrMess['message_class'] = " success";
            $arrMessage[] = $arrMess;
            $_SESSION['TL_SER_REGISTERED'] = false;
        }

        if ($_SESSION['TL_SER_UNREGISTERED']) {
            global $objPage;

            // Do not index the page
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            $objTemplate->blnShowForm = false;
            $objTemplate->blnShowDiscardForm = false;
            $arrMess['message'] = $GLOBALS['TL_LANG']['MSC']['ser_unregister_success'];
            $arrMess['message_class'] = " success";
            $arrMessage[] = $arrMess;
            $_SESSION['TL_SER_UNREGISTERED'] = false;
        }
        if ($_SESSION['TL_SER_ERRORCAPTCHA']) {
          $objTemplate->errorCaptcha = true;
          $_SESSION['TL_SER_ERRORCAPTCHA'] = false;
        }


        // Build the form
        $objTemplate->checkbox_label = $GLOBALS['TL_LANG']['MSC']['ser_checkbox_label'];
        $objTemplate->submit = $GLOBALS['TL_LANG']['MSC']['ser_submit'];

        $objTemplate->unregister_checkbox_label = $GLOBALS['TL_LANG']['MSC']['ser_checkbox_label_unregister'];
        $objTemplate->unsubmit = $GLOBALS['TL_LANG']['MSC']['ser_unregister'];

        $objTemplate->message = $arrMessage;

        //print_r($objTemplate);

        return $objTemplate->parse();
    }

    protected function checkPlaces($id, $intPlaces)
    {
        $objPlaces = $this->Database->execute("SELECT SUM(quantity) AS reg_places FROM tl_event_registrations WHERE pid=".$id);

        if ($objPlaces->reg_places<$intPlaces) {
            return $intPlaces - $objPlaces->reg_places;
        } else {
            return false;
        }
    }

    protected function checkRegistration($userId, $intEventId)
    {
        $objPlaces = $this->Database->prepare("SELECT * FROM tl_event_registrations WHERE userId=? AND pid=?")->execute($userId, $intEventId);

        if ($objPlaces->numRows>0) {
            return true;
        } else {
            return false;
        }
    }

    protected function registerUser($objEvent)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "secret=".$GLOBALS['TL_CONFIG']['ERMSecretKey']."&response=".$_POST['g-recaptcha-response'],
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if(!json_decode($response,true)['success']) {
          $objTemplate = new \FrontendTemplate('simple_events_registration_form');
          $_SESSION['TL_SER_ERRORCAPTCHA'] = true;
          $this->reload();
        }
        else {
          $arrSet = array(
              'pid'        => $objEvent->id,
              'tstamp'    => time(),
              'firstname' => $_POST['vorname'],
              'lastname'    => $_POST['nachname'],
              'email'        => $_POST['email'],
              'anonym'    => 0
          );
          $intQuantity = $this->Input->post('quantity_select') ? $this->Input->post('quantity_select') : 1;
          if ($this->ser_quantity) {
              $arrSet['quantity'] = $intQuantity;
          }

          $this->Database->prepare("INSERT INTO tl_event_registrations %s")->set($arrSet)->execute();



          // Send notification
          $intPlaces = $this->checkPlaces($objEvent->id, $objEvent->ser_places);
          $strSql = $intPlaces ? "SELECT ser_confirm_subject AS subject, ser_confirm_text AS text, ser_confirm_html AS html FROM tl_calendar WHERE id=?" : "SELECT ser_wait_subject AS subject, ser_wait_text AS text, ser_wait_html AS html FROM tl_calendar WHERE id=?";
          $objMailText = \Database::getInstance()->prepare($strSql)->execute($objEvent->pid);
          $objEmail = new \Email();
          $strFrom = $GLOBALS['TL_CONFIG']['adminEmail'];
          $strNotify = $objEvent->ser_email != "" ? $objEvent->ser_email : $GLOBALS['TL_CONFIG']['adminEmail'];

          $span = \Calendar::calculateSpan($objEvent->startTime, $objEvent->endTime);

          // Get date
          if ($span > 0) {
              $objEvent->date = \Date::parse($GLOBALS['TL_CONFIG'][($objEvent->addTime ? 'datimFormat' : 'dateFormat')], $objEvent->startTime) . ' - ' . \Date::parse($GLOBALS['TL_CONFIG'][($objEvent->addTime ? 'datimFormat' : 'dateFormat')], $objEvent->endTime);
          } elseif ($objEvent->startTime == $objEvent->endTime) {
              $objEvent->date = \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $objEvent->startTime) . ($objEvent->addTime ? ' (' . \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $objEvent->startTime) . ')' : '');
          } else {
              $objEvent->date = \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $objEvent->startTime) . ($objEvent->addTime ? ' (' . \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $objEvent->startTime) . ' - ' . \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $objEvent->endTime) . ')' : '');
          }

          $notifyText = $intPlaces ? $GLOBALS['TL_LANG']['MSC']['ser_notify_mail'] : $GLOBALS['TL_LANG']['MSC']['ser_waitinglist_mail'];
          $notifySubject = $intPlaces ? $GLOBALS['TL_LANG']['MSC']['ser_register_subject'] : $GLOBALS['TL_LANG']['MSC']['ser_waitinglist_subject'];

          $messageText = $this->replaceInserts($objEvent, html_entity_decode($objMailText->text), $intQuantity);
          $messageHTML = $this->replaceInserts($objEvent, html_entity_decode($objMailText->html), $intQuantity);
          $notifyText = $this->replaceInserts($objEvent, $notifyText, $intQuantity);
          $objEmail->from = $strFrom;
          $objEmail->subject = $this->replaceInserts($objEvent, html_entity_decode($objMailText->subject), $intQuantity);
          $objEmail->text = $messageText;
          $objEmail->html = $messageHTML;
          $objEmail->sendTo($_POST['email']);
          $objEmail->sendTo($strNotify);
          $_SESSION['TL_SER_REGISTERED'] = true;
          $this->reload();
      }
    }

    protected function unregisterUser($objEvent)
    {
        $this->Database->prepare("DELETE FROM tl_event_registrations WHERE pid=? AND userId=?")->execute($objEvent->id, $this->User->id);

        // Send notification
        $objEmail = new \Email();
        $strFrom = $GLOBALS['TL_CONFIG']['adminEmail'];
        $strNotify = $objEvent->ser_email != "" ? $objEvent->ser_email : $GLOBALS['TL_CONFIG']['adminEmail'];

        $span = \Calendar::calculateSpan($objEvent->startTime, $objEvent->endTime);

        // Get date
        if ($span > 0) {
            $objEvent->date = $this->parseDate($GLOBALS['TL_CONFIG'][($objEvent->addTime ? 'datimFormat' : 'dateFormat')], $objEvent->startTime) . ' - ' . $this->parseDate($GLOBALS['TL_CONFIG'][($objEvent->addTime ? 'datimFormat' : 'dateFormat')], $objEvent->endTime);
        } elseif ($objEvent->startTime == $objEvent->endTime) {
            $objEvent->date = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objEvent->startTime) . ($objEvent->addTime ? ' (' . $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objEvent->startTime) . ')' : '');
        } else {
            $objEvent->date = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objEvent->startTime) . ($objEvent->addTime ? ' (' . $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objEvent->startTime) . ' - ' . $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objEvent->endTime) . ')' : '');
        }

        $messageText = $this->replaceInserts($objEvent, $GLOBALS['TL_LANG']['MSC']['ser_user_mail_unregister']);
        $notifyText = $this->replaceInserts($objEvent, $GLOBALS['TL_LANG']['MSC']['ser_notify_mail_unregister']);

        $objEmail->from = $strFrom;
        $objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['ser_register_subject_unregister'], html_entity_decode($objEvent->title));

        $objEmail->text = $messageText;
        $objEmail->html = nl2br($messageText);
        $objEmail->sendTo($this->User->email);

        $objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['ser_register_notify_subject_unregister'], html_entity_decode($objEvent->title));
        $objEmail->text = $notifyText;
        $objEmail->html = nl2br($notifyText);
        $objEmail->sendTo($strNotify);

        $_SESSION['TL_SER_UNREGISTERED'] = true;
        $this->reload();
    }

    protected function replaceInserts($objEvent, $text='')
    {
        global $objPage;

        $tags = array();
        preg_match_all('/{{[^{}]+}}/i', $text, $tags);

        foreach ($tags[0] as $tag) {
            $elements = explode('::', str_replace(array('{{', '}}'), array('', ''), $tag));
            $strValue = '';
            switch (strtolower($elements[0])) {
                // Form
                case 'user':
                    $strValue = $_POST[$elements[1]];
                    break;
                case 'event':
                    if ($elements[1] == 'url') {
                        $strUrl = $this->generateFrontendUrl(array('id'=>$objPage->id,'alias'=>$objPage->alias), '/events/%s');
                        $strValue = $this->generateEventUrl($objEvent, $strUrl);
                    } else {
                        $strValue = $objEvent->$elements[1];
                    }
                    break;
                default:
                    $strValue = '';
                    break;
            }

            $text = str_replace($tag, $strValue, $text);
        }

        return $text;
    }

    /**
     * Generate a URL and return it as string
     * @param object
     * @param string
     * @return string
     */
    protected function generateEventUrl($objEvent, $strUrl)
    {
        // Link to default page
        if ($objEvent->source == 'default' || !strlen($objEvent->source)) {
            return $this->Environment->base . ampersand(sprintf($strUrl, ((!$GLOBALS['TL_CONFIG']['disableAlias'] && strlen($objEvent->alias)) ? $objEvent->alias : $objEvent->id)));
        }

        // Link to external page
        if ($objEvent->source == 'external') {
            $this->import('String');

            if (substr($objEvent->url, 0, 7) == 'mailto:') {
                $objEvent->url = 'mailto:' . $this->String->encodeEmail(substr($objEvent->url, 7));
            }

            return ampersand($objEvent->url);
        }

        // Fallback to current URL
        $strUrl = ampersand($this->Environment->request, true);

        // Get internal page
        $objPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
                                  ->limit(1)
                                  ->execute($objEvent->jumpTo);

        if ($objPage->numRows) {
            return ampersand($this->generateFrontendUrl($objPage->fetchAssoc()));
        }

        return '';
    }
}
