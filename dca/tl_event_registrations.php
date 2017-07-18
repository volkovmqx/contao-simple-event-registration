<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005-2009 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  2010 - 2014 Felix Pfeiffer : Neue Medien
 * @author     Felix Pfeiffer
 * @package    simple_event_registration
 * @license    LGPL
 * @filesource
 */


/**
 * Table tl_event_registrations
 */
$GLOBALS['TL_DCA']['tl_event_registrations'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'ptable'					  => 'tl_calendar_events',
		'doNotCopyRecords'			  => true,
		'closed'			 		  => true,
		'onload_callback'			  => array(array('tl_event_registrations','checkClosed')),
		'onsubmit_callback'			  => array(array('tl_event_registrations','saveName')),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index'
            )
        )
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('tstamp'),
			'headerFields'            => array('title','ser_places','ser_date'),
			'flag'                    => 6,
			'panelLayout'			  => 'limit',
			'child_record_callback'	  => array('tl_event_registrations','showItems')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'serexport' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_event_registrations']['serexport'],
				'href'                => 'key=serexport',
				'class'               => 'header_css_import',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_event_registrations']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
            'activateRegistration' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_event_registrations']['activateRegistration'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('tl_event_registrations', 'toggleIcon')
            ),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_event_registrations']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),
	// Palettes
	'palettes' => array
	(
		'default'                     => '{settings_legend},quantity;{name_legend},firstname,lastname;{email_legend},email;'
	),

	'fields' => array(
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'userId' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_event_registrations']['userId'],
			'search'                  => true,
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array(),
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'anonym' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_event_registrations']['anonym'],
			'search'                  => true,
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array(),
            'sql'                     => "char(1) NOT NULL default '0'"
		),
		'firstname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_event_registrations']['firstname'],
			'search'                  => true,
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'lastname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_event_registrations']['lastname'],
			'search'                  => true,
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('tl_class'=>'w50','mandatory'=>true),
            'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'email' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_event_registrations']['email'],
			'search'                  => true,
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('tl_class'=>'w50','rgxp'=>'email'),
            'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'quantity' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_event_registrations']['quantity'],
			'default'                 => 1,
			'search'                  => true,
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'		  => array('tl_event_registrations','listPlaces'),
			'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "int(4) unsigned NOT NULL default '1'"
		),
		'waitinglist' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_event_registrations']['waitinglist'],
			'search'                  => true,
			'exclude'                 => true,
			'inputType'               => 'checkbox',
            'sql'                     => "char(1) NOT NULL default '0'"
		)
	)
);

class tl_event_registrations extends Backend
{

	public function checkClosed(DataContainer $dc)
	{
		$id= $this->Input->get('act') == 'create' ? $this->Input->get('pid') : $dc->id;

		$places = $this->checkPlaces($id);

		if($places)
		{
			$GLOBALS['TL_DCA']['tl_event_registrations']['config']['closed'] = false;
			$GLOBALS['TL_DCA']['tl_event_registrations']['list']['global_operations']['anonymregister'] = array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_event_registrations']['anonymregister'],
				'href'                => 'key=anonymregister',
				'class'               => 'header_css_import',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			);
		}

	}


	public function saveName(DataContainer $dc)
	{
		$id= $dc->id;

		$objUser = $this->Database->prepare("UPDATE tl_event_registrations %s WHERE id=?")->set(array('anonym'=>1))->execute($id);

	}


	/**
	 * Show Entrys
	 * @param array
	 * @return string
	 */
	public function showItems($arrRow)
	{
		if($arrRow['anonym'] != 1)
		{
			$strName = 'Anonyme Anmeldung';
			if($arrRow['lastname'] != '')
			{
				$strName = ( $arrRow['firstname'] != '' ? $arrRow['firstname'] . ' ' : '' ) . $arrRow['lastname'];
			}
			return '<p style="">' . $arrRow['quantity'] . ' - ' .$strName.', ' . \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'],$arrRow['tstamp']) . '</p> '."\n";
		}

		$objUser = $this->Database->prepare("SELECT * FROM tl_member WHERE ID=?")->execute($arrRow['userId']);

        $strClass = '';
        if($arrRow['waitinglist']==1)
        {
            $strClass="wtlist";
        }

		return '
<p class="'.$strClass.'">' . $arrRow['quantity'] . ' - ' . $objUser->firstname . ' ' . $objUser->lastname . ', <a href="contao/main.php?do=member&act=edit&id='.$objUser->id.'">' . $objUser->username . '</a>, ' . \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'],$arrRow['tstamp']) . '</p>' . "\n";

	}

	/* Anonymen User registrieren */
	public function anonymregister()
	{

		if ($this->Input->get('key') != 'anonymregister')
		{
			return '';
		}
		$message = false;
		$number=1;
		$places = $this->checkPlaces();

		if(!$places) $message = sprintf('<p class="tl_error">%s</p>%s', $GLOBALS['TL_LANG']['MSC']['ser_no_places'], "\n");

		// Import
		if ($this->Input->post('FORM_SUBMIT') == 'tl_event_registrations_anonymregister' && $this->Input->post('number') != 0)
		{

			$pid=$this->Input->get('id');
			$number = $this->Input->post('number');

			if($number > $places)
			{
				$message = sprintf('<p class="tl_error">%s</p>%s', sprintf($GLOBALS['TL_LANG']['MSC']['ser_to_much_places'],$places), "\n");
				$number=$places;
			}
			else
			{
				for($i=1;$i<=$number;$i++)
				{
					$arrData = array(
						'pid'=>$pid,
						'tstamp'=>time(),
						'anonym'=>1
					);

					$this->Database->prepare("INSERT INTO tl_event_registrations %s")->set($arrData)->execute()->query;
				}

				$message = sprintf('<p class="tl_message">%s</p>%s', sprintf($GLOBALS['TL_LANG']['MSC']['ser_anonym_set'],$number), "\n");

				$number=1;
			}

		}

		$places = $this->checkPlaces();
		// Return form
		$return = '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=anonymregister', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_event_registrations']['anonymregister'][1].'</h2>'. ($message?'<div class="tl_listing_container">'.$message.'</div>' : '');

	if($places)
	{
		$return .= '
<form action="'.ampersand($this->Environment->request, true).'" id="tl_event_registrations_anonymregister" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_event_registrations_anonymregister" />
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">

<div class="tl_tbox block">
'.sprintf($GLOBALS['TL_LANG']['MSC']['ser_anonym_max places'],$places).'
<h3><label for="ctrl_anzahl">'.$label.'</label></h3>
  <input name="number" id="ctrl_anzahl" class="tl_text" value="'.$number.'" maxlength="255" onfocus="Backend.getScrollOffset();" type="text">

</div>

</div>
<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" alt="import style sheet" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_event_registrations']['anonymregister'][0]).'" />
</div>

</div>
</form>
</div>';
	}
	else
	{
		$return .= '
		<div class="tl_formbody_submit"></div>';
	}

return $return;
	}

	protected function checkPlaces($id=0)
	{
		$id= $id != 0 ? $id : $this->Input->get('id');
		$objEvent = $this->Database->prepare("SELECT ser_places FROM tl_calendar_events WHERE id=?")->execute($id);
		$objPlaces = $this->Database->execute("SELECT SUM(quantity) AS reg_places FROM tl_event_registrations WHERE pid=".$id);

		if($objPlaces->numRows && $objPlaces->reg_places<$objEvent->ser_places) return $objEvent->ser_places - $objPlaces->reg_places;
		else return false;

	}

	/**
	 * Return a form to choose a CSV file and import it
	 * @param object
	 * @return string
	 */
	public function exportRegisteredUsers(DataContainer $dc)
	{

		$strRedirectUrl = ampersand(str_replace('&key=serexport', '', $this->Environment->request));
		if ($this->Input->get('key') != 'serexport')
		{
			$this->redirect($strRedirectUrl);
		}

		$pid=$this->Input->get('id');

		$objUsers = $this->Database->prepare("SELECT * FROM tl_event_registrations WHERE pid=? AND ( userId!=0 OR (anonym !=1 AND lastname != ''))")
								   ->execute($pid);

		if($objUsers->numRows == 0) $this->redirect($strRedirectUrl);

		$fields = $GLOBALS['BE_MOD']['content']['calendar']['serexportfields'];

		$strUserfields = implode(',',$fields['user']);
		// get records
		$arrExport = array();

		$objEventRow = $this->Database->prepare("SELECT *, (SELECT jumpTo FROM tl_calendar WHERE tl_calendar.id=tl_calendar_events.pid) AS jumpTo, (SELECT id FROM tl_page WHERE tl_page.id=jumpTo) AS pageId, (SELECT alias FROM tl_page WHERE tl_page.id=jumpTo) AS pageAlias FROM tl_calendar_events WHERE id=?")
					                  ->execute($pid);
		$span = \Calendar::calculateSpan($objEventRow->startTime, $objEventRow->endTime);

		// Get date
		if ($span > 0)
		{
			$objEventRow->date = \Date::parse($GLOBALS['TL_CONFIG'][($objEventRow->addTime ? 'datimFormat' : 'dateFormat')], $objEventRow->startTime) . ' - ' . \Date::parse($GLOBALS['TL_CONFIG'][($objEventRow->addTime ? 'datimFormat' : 'dateFormat')], $objEventRow->endTime);
		}
		elseif ($objEventRow->startTime == $objEventRow->endTime)
		{
			$objEventRow->date = \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $objEventRow->startTime) . ($objEventRow->addTime ? ' (' . \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $objEventRow->startTime) . ')' : '');
		}
		else
		{
			$objEventRow->date = \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $objEventRow->startTime) . ($objEventRow->addTime ? ' (' . \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $objEventRow->startTime) . ' - ' . \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $objEventRow->endTime) . ')' : '');
		}

		$objEventRow->url = $this->generateEventUrl($objEventRow,$this->generateFrontendUrl(array('id'=>$objEventRow->pageId,'alias'=>$objEventRow->pageAlias), 'events/%s'));

		$arrEvents = array();
		foreach($fields['event'] as $value)
		{
			$arrEvents[] = $objEventRow->$value;
		}


		while($objUsers->next())
		{
			if($objUsers->anonym != 0)
			{
				$objUserRow = $this->Database->prepare("SELECT ".$strUserfields." FROM tl_member WHERE id=?")
							->execute($objUsers->userId);
				$arrValues = $objUserRow->row();
			}
			else
			{
				foreach($fields['anuser'] as $v)
				{
					$arrValues[$v] = $objUsers->$v;
				}
			}

			$arrExport[] = array_merge($arrValues,$arrEvents);
		}

		$fieldlabels = array();
		$this->loadLanguageFile('tl_member');
		$this->loadLanguageFile('tl_calendar_events');
		foreach($fields['user'] as $value)
		{
			$fieldlabels[] = $GLOBALS['TL_LANG']['tl_member'][$value][0] != "" ? $GLOBALS['TL_LANG']['tl_member'][$value][0] : $value;
		}
		foreach($fields['event'] as $value)
		{
			$fieldlabels[] = $GLOBALS['TL_LANG']['tl_calendar_events'][$value][0] != "" ? $GLOBALS['TL_LANG']['tl_calendar_events'][$value][0] : $value;
		}


		// start output
		$exportFile =  'simple_event_register_' . date("Ymd-Hi");

		header('Content-Type: application/csv');
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="' . $exportFile .'.csv"');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Expires: 0');

		$output = '';
		$output .= html_entity_decode(utf8_decode('"' . join('";"', $fieldlabels).'"' . "\n"));



		foreach ($arrExport as $export)
		{
			$export['gender'] = $GLOBALS['TL_LANG']['MSC'][$export['gender']];
			$output .= html_entity_decode(utf8_decode('"' . join('";"', $export).'"' . "\n"));
		}

		echo $output;
		exit;

	}

	/**
	 * Generate a URL and return it as string
	 * @param object
	 * @param string
	 * @return string
	 */
	protected function generateEventUrl(Database_Result $objEvent, $strUrl)
	{
		// Link to default page
		if ($objEvent->source == 'default' || !strlen($objEvent->source))
		{
			return $this->Environment->base . ampersand(sprintf($strUrl, ((!$GLOBALS['TL_CONFIG']['disableAlias'] && strlen($objEvent->alias)) ? $objEvent->alias : $objEvent->id)));
		}

		// Link to external page
		if ($objEvent->source == 'external')
		{
			$this->import('String');

			if (substr($objEvent->url, 0, 7) == 'mailto:')
			{
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

		if ($objPage->numRows)
		{
			return ampersand($this->generateFrontendUrl($objPage->fetchAssoc()));
		}

		return '';
	}


	public function listPlaces()
	{
		$pid=$this->Input->get('pid');
		$intRest = $this->checkPlaces($pid);
		$arrReturn = array();
		for($i=1;$i<=$intRest+1;$i++)
		{
			$arrReturn[$i] = $i;
		}

		return $arrReturn;

	}

    /**
     * Return the "toggle visibility" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen($this->Input->get('tid')))
        {
            $this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 1));
            $this->redirect($this->getReferer());
        }
        if ($row['waitinglist']==1)
        {
            $href .= '&amp;tid='.$row['id'].'&amp;state=0';
            $icon = 'invisible.gif';
            return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
        }

        return '';

    }

    /**
     * Disable/enable a user group
     * @param integer
     * @param boolean
     */
    public function toggleVisibility($intId, $blnVisible)
    {

        $this->createInitialVersion('tl_event_registrations', $intId);

        // Update the database
        $this->Database->prepare("UPDATE tl_event_registrations SET tstamp=". time() .", waitinglist=0 WHERE id=?")
            ->execute($intId);

        $this->createNewVersion('tl_event_registrations', $intId);

    }
}

?>
