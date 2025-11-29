<?php
/**
 * XmlHandler - helper to read/write SimpleXML safely
 * Avoids exposing raw XML via web; rely on PHP to parse.
 */

class XmlHandler {
    private $filePath;

    public function __construct($filePath) {
        $this->filePath = $filePath;
    }

    public function read() {
        if (!file_exists($this->filePath)) {
            return false;
        }
        // Use file locking to reduce race conditions
        $fp = fopen($this->filePath, 'r');
        if (!$fp) return false;
        flock($fp, LOCK_SH);
        $contents = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($contents);
        if ($xml === false) {
            return false;
        }
        return $xml;
    }

    public function write($xmlString) {
        // write string to file safely
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $fp = fopen($this->filePath, 'c');
        if (!$fp) return false;
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        fwrite($fp, $xmlString);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    public function saveSimpleXML(SimpleXMLElement $xml) {
        return $this->write($xml->asXML());
    }
}

// Example usage (commented):
// $xh = new XmlHandler(__DIR__ . '/../data/users.xml');
// $users = $xh->read();
?>