<?php

$classes = array(
    "dky\\BonusesTable" => "lib/Bonuses.php",
    "dky\\BonusesHistoryTable" => "lib/BonusesHistory.php",
    "dky\\Options" => "lib/Options.php",
    "dky\\SessionStorage" => "lib/SessionStorage.php",
    "dky\\Tools" => "lib/Tools.php",
    "dky\\EventsHandlers" => "lib/EventsHandlers.php",
    "dky\\BonusesAgents" => "lib/BonusesAgents.php",
);

CModule::AddAutoloadClasses("dky.bonuses", $classes);
