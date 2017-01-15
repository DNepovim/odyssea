<?php

require_once '../../../../vendor/autoload.php';
require_once __DIR__ . '/OdysseaMailer.php';
$mailer = new OdysseaMailer();
$mailer->getCredentials();
