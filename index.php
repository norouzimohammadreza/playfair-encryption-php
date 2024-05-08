<?php

function prepareText($text) {
    // تبدیل حروف کوچک به حروف بزرگ
    $text = strtoupper($text);
    
    // حذف همه کاراکترهای غیر الفبایی
    $text = preg_replace("/[^A-Z]/", '', $text);
    
    // افزودن حرف اضافی برای جفت‌شدن حروف یکسان
    $text = str_split($text, 2);
    foreach ($text as $key => $pair) {
        if (strlen($pair) == 1) {
            $text[$key] .= 'X';
        }
    }
    
    return $text;
}

function playfairCipher($text, $key, $cipherMode) {
    $cipher = new PlayfairCipher($key);
    
    if ($cipherMode == 'encrypt') {
        return $cipher->encrypt($text);
    } elseif ($cipherMode == 'decrypt') {
        return $cipher->decrypt($text);
    } else {
        return null;
    }
}

class PlayfairCipher {
    private $keySquare;
    
    function __construct($key) {
        $this->keySquare = $this->generateKeySquare($key);
    }
    
    function generateKeySquare($key) {
        $key = strtoupper($key);
  
        $key = str_replace('J', 'I', $key); // جایگزینی J با I
        
        $keySquare = array();
        $keyArray = array();
        
        for ($i = 0; $i < strlen($key); $i++) {
            
            if (!in_array($key[$i], $keyArray)) {
                array_push($keyArray, $key[$i]);
            }
            
        }
        
        $alphabet = range('A', 'Z');

       
        foreach ($alphabet as $letter) {
            if ($letter != 'J' && !in_array($letter, $keyArray)) {
                array_push($keyArray, $letter);
            }
            
        }

        $keySquare = array_chunk($keyArray, 5);
        return $keySquare;
    }
    
    function encrypt($text) {
        $output = '';
        
        foreach ($text as $pair) {

            $char1 = $pair[0];
            $char2 = $pair[1];
            
            $char1Pos = $this->findCharInKeySquare($char1);
            $char2Pos = $this->findCharInKeySquare($char2);
            
            if ($char1Pos[0] == $char2Pos[0]) {
                $output .= $this->getHorizontalNeighbours($char1Pos, $char2Pos, 1);
            } elseif ($char1Pos[1] == $char2Pos[1]) {
                $output .= $this->getVerticalNeighbours($char1Pos, $char2Pos, 1);
            } else {
                $output .= $this->getRectangleNeighbours($char1Pos, $char2Pos, 1);
            }
        }
        
        return $output;
    }
    
    function decrypt($text) {
        $output = '';
        
        foreach ($text as $pair) {
            $char1 = $pair[0];
            $char2 = $pair[1];
            
            $char1Pos = $this->findCharInKeySquare($char1);
            $char2Pos = $this->findCharInKeySquare($char2);
            
            if ($char1Pos[0] == $char2Pos[0]) {
                $output .= $this->getHorizontalNeighbours($char1Pos, $char2Pos, -1);
            } elseif ($char1Pos[1] == $char2Pos[1]) {
                $output .= $this->getVerticalNeighbours($char1Pos, $char2Pos, -1);
            } else {
                $output .= $this->getRectangleNeighbours($char1Pos, $char2Pos, -1);
            }
        }
        
        return $output;
    }
    
    function findCharInKeySquare($char) {
        foreach ($this->keySquare as $rowKey => $row) {
            foreach ($row as $colKey => $col) {
                if ($col == $char) {
                    return array($rowKey, $colKey);
                }
            }
        }
    }
    
    function getHorizontalNeighbours($pos1, $pos2, $direction) {
        $output = '';
        
        $output .= $this->keySquare[$pos1[0]][($pos1[1] + $direction + 5) % 5];
        $output .= $this->keySquare[$pos2[0]][($pos2[1] + $direction + 5) % 5];
        
        return $output;
    }
    
    function getVerticalNeighbours($pos1, $pos2, $direction) {
        $output = '';
        
        $output .= $this->keySquare[($pos1[0] + $direction + 5) % 5][$pos1[1]];
        $output .= $this->keySquare[($pos2[0] + $direction + 5) % 5][$pos2[1]];
        
        return $output;
    }
    
    function getRectangleNeighbours($pos1, $pos2, $direction) {
        $output = '';
        
        $output .= $this->keySquare[$pos1[0]][$pos2[1]];
        $output .= $this->keySquare[$pos2[0]][$pos1[1]];
        
        return $output;
    }
}
?>
<form action="index.php" method="post">
<input type="text" name="text" placeholder="Enter your Value">
<input type="submit" value="submit">
</form>


<?php
// مثال اجرای برنامه
$key = 'KEYWORD';
if(isset($_POST['text'])){
$text = $_POST['text'];
$text = prepareText($text);

echo 'Text: ' . implode(' ', $text) . PHP_EOL.'</br>';

$encryptedText = playfairCipher($text, $key, 'encrypt');
echo 'Encrypted Text: ' . $encryptedText . PHP_EOL .'</br>';

$decryptedText = playfairCipher(str_split($encryptedText, 2), $key, 'decrypt');
$decryptedText = str_split($decryptedText);

if(end($decryptedText)=='X'){
    array_pop($decryptedText);
}
$decryptedText = implode($decryptedText);

echo 'Decrypted Text: ' .  $decryptedText . PHP_EOL.'</br>';
}
?>