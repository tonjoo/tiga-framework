<?php

// Echo anything that buffered when the controller method is run 
echo View::getBuffer();
// Send content which held by Response Object
echo View::sendResponse();
