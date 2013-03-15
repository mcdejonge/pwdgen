<?php

class PwdGen {

    /**************************************************************************
     *                                                                        *
     *  Settings. These have sane default values but can be changed.          *
     *                                                                        *
     *************************************************************************/

    // The minimum length for a password.
    protected $minLength = 12;
    // The number of digits in the password.
    protected $numDigits = 2;
    // The number of punctuation symbols.
    protected $numPunctuationSymbols = 1;
    // The characters to use as punctuation.
    protected $punctuationSymbols = array(
        '!', '(', ')', '[', ']', ':', ';', ',', '?',
    );
    // Whether you want words and syllables to have their first characters
    // capitalized or not.
    protected $capitalizeWords = true;

    // The language to use for non-random, non-nonsense passwords. A file
    // words/[language].txt must exist for this language.
    protected $language = 'en';

    /**************************************************************************
     *                                                                        *
     *  Getters and setters for settings.                                     *
     *                                                                        *
     *************************************************************************/
    public function __set($property, $value) {

        // minLength
        if($property == 'minLength') {
            if(! preg_match('/^\d+$/', $value)
                || $value < 0) {
                    throw new Exception("Invalid value '$value' supplied for property $property.");
                }
            $this->minLength = $value;
            return;
        }

        // numDigits
        if($property == 'numDigits') {
            if(! preg_match('/^\d+$/', $value)
                || $value < 0) {
                    throw new Exception("Invalid value '$value' supplied for property $property.");
                }
            $this->numDigits = $value;
            return;
        }

        // numPunctuationSymbols
        if($property == 'numPunctuationSymbols') {
            if(! preg_match('/^\d+$/', $value)
                || $value < 0) {
                    throw new Exception("Invalid value '$value' supplied for property $property.");
                }
            $this->numPunctuationSymbols = $value;
            return;
        }

        // punctuation. Use addPunctuation() and removePunctuation() to add or
        // remove individual characters.
        if($property == 'punctuationSymbols') {
            if(! is_array($value)){
                throw new Exception("Invalid value supplied for property $property. Must be an array.");
            }
            foreach($value as $item) {
                if(strlen($item) != 1) {
                    throw new Exception("Invalid value supplied for property $property. Must be an array of single characters.");
                }
            }
            $this->punctuationSymbols = $value;
            return;
        }

        // capitalizeWords
        if($property == 'capitalizeWords') {
            if($value !== true && $value !== false) {
                throw new Exception("Invalid value supplied for property $property. Must be either true or false.");
            }
            $this->capitalizeWords = $value;
            return;
        }

        // language
        if($property == 'language') {
            if(! is_file("words/" . $value . ".txt")) {
                throw new Exception("Invalid value supplied for property $property. There must be a file words/$value.txt for it.");
            }
            $this->language = $value;
            return;
        }

        throw new Exception("Attempt to set unavailable property $property.");
    }

    /**
     * Add a character to the list of punctuation symbols.
     */
    public function addPunctuationSymbol($symbol) {
        if(strlen($symbol) != 1){
            throw new Exception("Unable to add symbol '$symbol' to punctuation list. Must be a single character.");
        }
        if(! in_array($symbol, $this->punctuationSymbols)) {
            $this->punctuationSymbols[] = $symbol;
        }
    }

    /**
     * Remove a character from the list of punctuation symbols.
     */
    public function removePunctuationSymbol($symbol) {
        if(! in_array($symbol, $this->punctuationSymbols)) {
            return;
        }
        $this->punctuationSymbols = array_splice(
            $this->punctuationSymbols,
            array_search($symbol, $this->punctuationSymbols),
            1);
    }

    /**
     * Getters.
     */
    public function __get($property) {

        if($property == 'minLength') {
            return $this->minLength;
        }

        if($property == 'numDigits') {
            return $this->numDigits;
        }

        if($property == 'numPunctuationSymbols') {
            return $this->numPunctuationSymbols;
        }

        if($property == 'punctuationSymbols') {
            return $this->punctuationSymbols;
        }

        if($property == 'capitalizeWords') {
            return $this->capitalizeWords;
        }

        if($property == 'language') {
            return $this->language;
        }

        throw new Exception("Unable to retrieve non-existent property $property.");
    }

    /**************************************************************************
     *                                                                        *
     *  Publicly available methods.                                           *
     *                                                                        *
     *************************************************************************/


    /**
     * Generate a password that consists of randomly generated characters.
     *
     * @param numCharacters optionally supply the number of characters.
     */
    function generateRandom($numCharacters = null) {
        if($numCharacters === null) {
            $numCharacters = $this->minLength - $this->numDigits - $this->numPunctuation;
        }
        if(! preg_match('/^\d+/', $numCharacters)) {
            throw new Exception("Invalid number of characters supplied to method generateRandom.");
        }

        if($numCharacters < 0) {
            $numCharacters = 0;
        }

        $digitsPosition = 0;
        if($numCharacters > 0) {
            $digitsPosition = rand(0, $numCharacters);
        }

        $punctuationPosition = 0;
        if($numCharacters > 0) {
            $punctuationPosition = rand(0, $numCharacters);
        }

        $digits = '';
        for($i = 0; $i < $this->numDigits; $i++) {
            $digits .= rand(0,9);
        }

        $punctuation = '';
        for($i = 0; $i < $this->numPunctuationSymbols; $i++) {
            $punctuation .= $this->punctuationSymbols[rand(0, count($this->punctuationSymbols) - 1)];
        }

        if($numCharacters == 0) {
            return $digits . $punctuation;
        }

        $chars = array_merge(range(0,9), range('a', 'z'), range('A', 'Z'));

        $string = '';
        for($i = 0; $i < $numCharacters; $i++) {
            $string .=  $chars[rand(0, count($chars) - 1)];
            if($i == $digitsPosition) {
                $string .= $digits;
            }
            if($i == $punctuationPosition) {
                $string .= $punctuation;
            }
        }
        return $string;
    }

    /**
     * Generate a password consisting of words in the current language.
     */
    public function generateWordPassword() {
        return $this->generatePasswordUsingGenerator('getWordForLanguage');
    }

    /**
     * Generate a password consisting of nonsense syllables.
     */
    public function generateNonsensePassword() {
        return $this->generatePasswordUsingGenerator('generateSyllable');
    }

    /**
     * Generate a password using the supplied function to generate words.
     *
     * @param wordGeneratingFunction : a function that will generate words.
     */
    public function generatePasswordUsingGenerator($wordGeneratingFunction) {

        $string = '';
        $previousEndPosition = 0;
        $minLengthForWords = $this->minLength - $this->numDigits - $this->numPunctuationSymbols;
        while(strlen($string) < $minLengthForWords) {
            $previousEndPosition = strlen($string);
            $word = '';
            // My own host does not have a PHP version that is new enough
            // to allow calls to object methods in anonymous functions. Hence
            // this ugly workaround.
            if(is_string($wordGeneratingFunction)) {
                if($wordGeneratingFunction == 'getWordForLanguage') {
                    $word = $this->getWordForLanguage();
                }
                else if ($wordGeneratingFunction == 'generateSyllable') {
                    $word = $this->generateSyllable();
                }
            }
            else {
                $word = $wordGeneratingFunction();
            }
            if($this->capitalizeWords) {
                $word = ucfirst($word);
            }
            $string .= $word;
        }

        return $this->injectPunctuationAndDigits($string, $previousEndPosition);
    }

    /**************************************************************************
     *                                                                        *
     *  protected methods.                                                      *
     *                                                                        *
     *************************************************************************/

    protected function injectPunctuationAndDigits($string, $position) {
        $digits = '';
        for($i = 0; $i < $this->numDigits; $i++) {
            $digits .= rand(0,9);
        }

        $punctuation = '';
        for($i = 0; $i < $this->numPunctuationSymbols; $i++) {
            $punctuation .= $this->punctuationSymbols[rand(0, count($this->punctuationSymbols) - 1)];
        }

        $segment1 = substr($string, 0, $position );
        $segment2 = substr($string, $position );

        return $segment1
            . $digits
            . $punctuation
            . $segment2;

    }



    /**
     * Generate a nonsense syllable consisting of a consonant, the given number of
     * vowels and another consonant.
     */
    protected function generateSyllable($numVowels = null) {
        if($numVowels === null) {
            $numVowels = rand(1,2);
        }
        $syllables = 'aeiou';
        $consonants = 'bcdfghjklmnpqrstvwxzy';

        $string = $consonants[rand(0, strlen($consonants) - 1)];
        for($i = 0; $i < $numVowels; $i++) {
            $string .= $syllables[rand(0, strlen($syllables) - 1)];
        }


        $string .= $consonants[rand(0, strlen($consonants - 1))];

        return $string;

    }

    /**
     * Retrieve a random word for the current language.
     */
    protected function getWordForLanguage() {

        $file = 'words/' . $this->language . '.txt';
        if(! is_file($file)) {
            throw new Exception('Word list file words/'
                . $this->language
                . '.txt does not exist.');
        }

        return $this->getWordFromFile($file);
    }

    /**
     * Retrieve a random word from a file.
     */
    public function getWordFromFile($file) {
        if(! is_file($file)) {
            throw new Exception("Word list file $file does not exist.");
        }
        return $this->readRandomLineFromFile($file);

    }

    /**
     * Read a random line from a file and return it.
     */
    protected function readRandomLineFromFile($file) {
        if(! file_exists($file)) {
            throw new Exception("Unable to read random line from non-existent file $file");
        }

        $randomLineNum = rand(0, $this->getNumLinesInFile($file) - 1);

        $handle = fopen($file, 'r') or die ("Unable to open file $file");
        if(! $handle ) {
            throw new Exception("Unable to obtain file handle for file $file");
        }

        $word = '';
        $lineNum = 0;
        while(! feof($handle)) {
            $line = fgets($handle);
            if($lineNum == $randomLineNum) {
                $word = trim($line);
                break;
            }
            $lineNum++;
        }
        fclose($handle);

        return $word;
    }

    /**
     * Return the number of lines in a given file. Memoized.
     */
    protected $numLinesPerFile = array(); // For memoization.
    function getNumLinesInFile($file) {
        if(isset($_numLinesPerFile[$file])) {
            return $_numLinesPerFile[$file];
        }

        if(! file_exists($file)) {
            throw new Exception("Unable to count lines in non-existent file $file");
        }

        $handle = fopen($file, 'r') or die ("Unable to open file $file for counting.");
        if(! $handle ) {
            throw new Exception("Unable to obtain file handle for file $file for counting.");
        }

        $numLines = 0;
        while(! feof($handle)) {
            $line = fgets($handle);
            $numLines++;
        }
        fclose($handle);
        return $numLines;
    }

}
