<?php
// index.php

class AsciiConverter
{
    // Convertir une chaîne de caractères en un entier pour la clé de chiffrement
    private static function convertKeyToInt($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('La clé doit être une chaîne de caractères.');
        }
        return crc32($key) % 256; // Utiliser le modulo 256 pour rester dans la plage valide
    }

    // Générer un sel unique basé sur la clé
    private static function generateSalt($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('La clé doit être une chaîne de caractères.');
        }
        return crc32($key) % 256;
    }

    // Mélanger les valeurs ASCII avec un facteur aléatoire pour éviter les répétitions
    private static function mixAsciiValue($value, $salt)
    {
        return ($value + $salt * 3) % 256;
    }

    // Méthode pour convertir une chaîne de valeurs ASCII en chaîne de caractères avec une clé
    public static function asciiToStringWithKey($asciiString, $key)
    {
        if (!is_string($asciiString) || !is_string($key)) {
            throw new InvalidArgumentException('Les paramètres doivent être des chaînes de caractères.');
        }

        $asciiArray = array_map('trim', explode(',', $asciiString));
        $string = '';
        $adjustedKey = self::convertKeyToInt($key);
        $salt = self::generateSalt($key);

        foreach ($asciiArray as $ascii) {
            if (is_numeric($ascii)) {
                // Ajuster la valeur en tenant compte du sel et du facteur de mélange
                $originalAscii = (int)$ascii - $adjustedKey;
                $originalAscii = self::mixAsciiValue($originalAscii, -$salt);

                // Vérifier si le code ASCII est inférieur à 0
                if ($originalAscii < 0) {
                    $originalAscii = 256 + $originalAscii; // Ramener dans la plage 0-255
                }

                $string .= chr($originalAscii);
            }
        }
        return $string;
    }

    // Méthode pour convertir une chaîne de caractères en valeurs ASCII en utilisant une clé de chiffrement
    public static function stringToAscii($string, $key)
    {
        if (!is_string($string) || !is_string($key)) {
            throw new InvalidArgumentException('Les paramètres doivent être des chaînes de caractères.');
        }

        $asciiArray = [];
        $length = strlen($string);
        $adjustedKey = self::convertKeyToInt($key);
        $salt = self::generateSalt($key);

        for ($i = 0; $i < $length; $i++) {
            $originalAscii = ord($string[$i]);
            $newAscii = self::mixAsciiValue($originalAscii + $adjustedKey, $salt);

            // Vérifier si le nouveau code ASCII dépasse 255
            if ($newAscii > 255) {
                $newAscii = $newAscii % 256; // Ramener dans la plage 0-255
            }

            $asciiArray[] = $newAscii;
        }

        return implode(',', $asciiArray);
    }

    // Méthode pour compter le nombre de caractères dans une chaîne
    public static function countCharacters($string)
    {
        if (!is_string($string)) {
            throw new InvalidArgumentException('Le paramètre doit être une chaîne de caractères.');
        }
        return strlen($string);
    }
}

// Exemple de données à envoyer
$string = "Ndenga ";
$key = "12357578a";

// Conversion de chaîne de caractères à ASCII chiffré
$encryptedAsciiString = AsciiConverter::stringToAscii($string, $key);

// Retourner les données JSON
header('Content-Type: application/json');
echo json_encode(['encrypted' => $encryptedAsciiString]);
?>
