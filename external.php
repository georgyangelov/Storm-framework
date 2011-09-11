<?php
// Load Storm Framework
require_once dirname(__FILE__).'/framework.php';
Storm::Init();

// Use the basic DI Container. (Loads only models)
require_once dirname(__FILE__).'/container/applicationBase.php';
Storm::SetContainer(new applicationBase());

// For external scripts
define("STORM_LOADED", true);
?>