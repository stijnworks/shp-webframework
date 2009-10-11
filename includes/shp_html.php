<?php

/**
 *  SHpartners Web Framework
 *
 *  (c) Copyright 2009 by SHpartners, http://www.shpartners.com
 *
 *  @author Pieter Claerhout <pieter@shpartners.com>
 *  @version 1.0
 */

// HTML abstraction layer
class SH_Html {
    
    // Output a HTML link
    public static function link($text, $url='#', $attributes=array()) {
        
        // TODO: Apply the routing if any
        
        // Update the attributes
        $attributes['href'] = $url;
        
        // Return the tag
        return self::_tag('a', $attributes, $text);
        
    }
    
    // Render a HTML tag
    protected static function _tag($tag, $attributes=array(), $content=false, $closeTag=true) {
        $output = "<{$tag} " .  self::_attributes($attributes);
        if ($content === false) {
            return $closeTag ? "{$output} />" : "{$output}>";
        } else {
            return $closeTag ? "{$output}>{$content}</{$tag}>" : "{$output}>{$content}";
        }
    }
    
    // Render a list of attributes
    protected static function _attributes($attributes=array()) {
        $out = array();
        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $value = htmlspecialchars($value);
                $out[] = "{$key}=\"{$value}\"";
            }
        }
        return implode(' ', $out);
    }
    
}