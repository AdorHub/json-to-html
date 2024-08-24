<?php

class Converter {

    private $type;
    private $payload;
    private $parameters;
    private $children;

    public function __construct($data)
    {
        $this->type = $data['type'] ?? null;
        $this->payload = $data['payload'] ?? null;
        $this->parameters = $data['parameters'] ?? null;
        $this->children = $data['children'] ?? null;
    }

    public function convertToHtml ()
    {
        $html = '';
        switch ($this->type) {
            case 'container':
                $html .= '<div';
                if (!empty($this->parameters)) {
                    $html .= ' style="' . $this->renderParameter() . '"';
                }
                if (!empty($this->payload)) {
                    $html .= $this->renderPayload();
                }
                $html .= '>';
                if (!is_null($this->children)) {
                    foreach ($this->children as $child) {
                        $child = new Converter($child);
                        $html .= $child->convertToHtml();
                    }
                }
                $html .= '</div>';
                break;

            case 'block':
                $html .= '<div';
                if (!empty($this->parameters)) {
                    $html .= ' style="' . $this->renderParameter() . '"';
                }
                if (!empty($this->payload)) {
                    $html .= $this->renderPayload();
                }
                $html .= '>';
                if (!is_null($this->children)) {
                    foreach ($this->children as $child) {
                        $child = new Converter($child);
                        $html .= $child->convertToHtml();
                    }
                }
                $html .= '</div>';
                break;

            case 'text':
                $html .= '<p';
                if (!empty($this->parameters)) {
                    $html .= ' style="' . $this->renderParameter() . '"';
                }
                if (!empty($this->payload)) {
                    $payloads = [];
                    foreach ($this->payload as $key => $value) {
                        if ($key == 'text') {
                            $text = $value;
                        } else {
                            if (!empty($value)) {
                                $payloads[] = $key . '="' . $value . '"';
                            } else {
                                $payloads[] = $key;
                            }
                        }                        
                    }
                    $html .= implode(' ', $payloads);
                }
                $html .= '>';
                if (!empty($text)) {
                    $html .= $text;
                }
                if (!is_null($this->children)) {
                    foreach ($this->children as $child) {
                        $html .= $child->convertToHtml();
                    }
                }
                $html .= '</p>';
                break;

            case 'image':
                $html .= '<img';
                if (!empty($this->parameters)) {
                    $html .= ' style="' . $this->renderParameter() . '"';
                }
                if (!empty($this->payload)) {
                    $payloads = [];
                    foreach ($this->payload as $key => $value) {
                        if ($key == 'link') {
                            continue;
                        }
                        else if ($key == 'image') {
                            foreach ($value as $innerkey => $innervalue) {
                                if ($innerkey == 'meta') {
                                    continue;
                                }
                                else if ($innerkey == 'url') {
                                    $url = $innervalue;
                                }
                                else {
                                    if (!empty($innervalue)) {
                                        $payloads[] = $innerkey . '="' . $innervalue . '"';
                                    } else {
                                        $payloads[] = $innerkey;
                                    }
                                }
                            }
                        }
                    }
                    $html .= implode(' ', $payloads);
                    $html .= ' src="' . $url . '" ';                    
                }
                $html .= '>';
                if (!is_null($this->children)) {
                    foreach ($this->children as $child) {
                        $html .= $child->convertToHtml();
                    }
                }
                $html .= '</img>';
                break;

            case 'button':
                $html .= '<button';
                if (!empty($this->parameters)) {
                    $html .= ' style="' . $this->renderParameter() . '"';
                }
                if (!empty($this->payload)) {
                    foreach ($this->payload as $key => $value) {
                        if ($key == 'text') {
                            $text = $value;
                        }
                        if ($key == 'link') {
                            foreach ($value as $innerkey => $innervalue) {
                                if ($innerkey == 'payload') {
                                    $url = $innervalue;
                                }
                            }
                        }
                        
                    }
                    $html .= ' onclick="window.location.href=' . "'" . $url . "'" . '"';
                }
                $html .= '>';
                if (!empty($text)) {
                    $html .= $text;
                }
                if (!is_null($this->children)) {
                    foreach ($this->children as $child) {
                        $html .= $child->convertToHtml();
                    }
                }
                $html .= '</button>';
                break;
        }
        return $html;
    }

    private function renderParameter ()
    {
        $parameters = [];
        foreach($this->parameters as $key => $value) {
            $property = preg_split('/(?=[A-Z])/', $key);                
            $cssProperty = strtolower(implode('-', $property));
            $parameters[] = $cssProperty . ':' . $value;
        }
        return implode('; ', $parameters);
    }

    private function renderPayload ()
    {
        $payloads = [];
        foreach ($this->payload as $key => $value) {
            if (!empty($value)) {
                $payloads[] = $key . '="' . $value . '"';
            } else {
                $payloads[] = $key;
            }
        }
        return implode(' ', $payloads);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_FILES)) {
        header('Location: index.html');
        die();
    }
    if ($_FILES['json']['type'] !== 'application/json') {
        header('Location: index.html');
        die();
    }

    $jsonString = file_get_contents($_FILES['json']['tmp_name']);
    $data = json_decode($jsonString, true);
    
    $root = new Converter($data);

    $html = $root->convertToHtml();    
    echo $html;

}

?>