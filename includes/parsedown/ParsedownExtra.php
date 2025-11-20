<?php
require_once 'Parsedown.php';

class ParsedownExtra extends Parsedown {
    protected function blockQuote($Line) {
        // ќбработка диалогов
        if (preg_match('/^Ч\s+/', $Line['text'])) {
            return array(
                'element' => array(
                    'name' => 'div',
                    'attributes' => array('class' => 'dialogue'),
                    'text' => ltrim($Line['text'], 'Ч ')
                )
            );
        }
        
        return parent::blockQuote($Line);
    }
}