<?php

/**
 * Password generator. Generates random strings and combinations of words 
 * (Dutch and English available, other languages possible if word lists are 
 * added).
 */

/************************************************************************************************
 *                                                                                              *
 *  Settings.                                                                                   *
 *                                                                                              *
 ***********************************************************************************************/
$NUMDIGITS = 2;
$NUMPUNCTUATION = 1;
$PUNCTUATION = array('!', '(', ')', '[', ']', ':', ';', ',', '?');
$LANGUAGES = array('en', 'nl'); // There must be a subdirectory below pwdgen_words for these.
$NUMCHARACTERSINRANDOMSTRING = 12;

/************************************************************************************************
 *                                                                                              *
 *  Not settings.                                                                               *
 *                                                                                              *
 ***********************************************************************************************/

include("inc/PwdGen.php");
$pwdGen = new PwdGen();
$pwdGen->numDigits = $NUMDIGITS;
$pwdGen->numPunctuationSymbols = $NUMPUNCTUATION;
$pwdGen->punctuationSymbols = $PUNCTUATION;

?>

<!DOCTYPE html>
<html>
<head>
<style type="text/css">
    body {
        text-align: center;
        font-size: 200%;
        line-height: 200%;
        padding: 5%;
        font-family: Courier New, courier, monospace;
    }

</style>
<title>Password generator</title>
</head>
<body>
<?php
foreach($LANGUAGES as $language) {
    $pwdGen->language = $language;
    echo $pwdGen->generateWordPassword();
    echo '<br />';
}
echo $pwdGen->generateNonsensePassword();
echo '<br />';
echo $pwdGen->generateRandom($NUMCHARACTERSINRANDOMSTRING);
echo '<br />';
?>

</body>
</html>
