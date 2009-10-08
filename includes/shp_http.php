<?php

/**
 *  SHpartners Web Framework
 *
 *  (c) Copyright 2009 by SHpartners, http://www.shpartners.com
 *
 *  @author Pieter Claerhout <pieter@shpartners.com>
 *  @version 1.0
 */

class SH_Http {
    
    // Perform a http redirect
    public static function redirect($url, $code=302) {
        header("HTTP/1.0 {$code} Found");
        header("Location: ${url}");
        die();
    }
    
    // Set the content type
    public static function contentType($type) {
        header("Content-Type: {$type}");
    }
    
    // Send JSON information
    public static function sendJson($data) {
        self::contentType('application/json');
        echo(json_encode($data));
    }
    
    // Send JSON information
    public static function sendXml($data) {
        self::contentType('text/xml');
        echo($data);
    }
    
    // Send a file
    protected function sendFile($filename, $contentType, $path) {
        header("Content-type: $contentType");
        header("Content-Disposition: attachment; filename=$filename");
        return readfile($path);
    }
    
    // Force a download
    protected function sendDownload($filename, $path) {
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename".";");
        header("Content-Transfer-Encoding: binary");
        return readfile($path);
    }
    
}