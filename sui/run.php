<?php

require '/var/www/phing/vendor/autoload.php';

Phing::startup();
Phing::setProperty('phing.home', getenv('PHING_HOME'));
Phing::setProperty('buildfile', 'test.xml');

Phing::fire( array(
    '-f', 'test.xml'

) );
Phing::shutdown();