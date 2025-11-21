<?php
require_once 'Parsedown.php';

class ParsedownExtra extends Parsedown {
    protected function blockQuote($Line) {
        if (preg_match('/^�\s+/', $Line['text'])) {
            return array(
                'element' => array(
                    'name' => 'div',
                    'attributes' => array('class' => 'dialogue'),
                    'text' => ltrim($Line['text'], '� ')
                )
            );
        }
        
        return parent::blockQuote($Line);
    }
}