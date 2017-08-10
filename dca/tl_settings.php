<?php
$GLOBALS['TL_DCA']['tl_settings']['palettes']["default"] =
  $GLOBALS['TL_DCA']['tl_settings']['palettes']["default"]
  .";{Event Registration Module},ERMSiteKey,ERMSecretKey";
array_insert($GLOBALS['TL_DCA']['tl_settings']['fields'],0,array(
    'ERMSiteKey'=>array(
        'label'=>array("ReCaptcha SiteKey"),
        'exclude'=>true,
        'inputType'=>'text',
        'eval'=> array('mandatory'=>true)
    ),
    'ERMSecretKey'=>array(
        'label'=>array("ReCaptcha Secret Key"),
        'exclude'=>true,
        'inputType'=>'text',
        'eval'=> array('mandatory'=>true)
    ),
));
