<?php
/**
 * @fileoverview This PHP-Class should only read a iCal-File (*.ics), parse it
 * and give an array with its content.
 *
 * @author: Frank Gregor
 * @version: 1.0
 * @website: http://programmschmie.de
 * @example
 *     $ical = new ical('MyCal.ics');
 *     print_r( $ical->get_event_array() );
 */

/**
 * This is the iCal-class
 * @param {string} filename The name of the file which should be parsed
 * @constructor
 */

class Template
{
	private /** @type {string} */ $_tpl_name = "";
	private /** @type {string} */ $_tpl_out = "";


    /** 
     * Constructor
     * 
     * @param {string}	$template 	This may be a filename URL/path or a simple string
     *                            	that is containing the hole template
     * @param {boolean} $fromFile	A boolean value that is indicating whether the $template comes from a file or a string.
     *                              Default is 'true' - the template comes from a file
     */ 
	public function Template( $template, $fromFile = true ) {
		// extract the name of the template
		$this->_tpl_name = basename($template);

		if (!$fromFile) {
			// template aus string einlesen
			$this->_tpl_out = $template;
		
		} else {
			// template aus datei einlesen
			if ($fd = @fopen($template, "r")) {
				$this->_tpl_out = fread($fd, filesize($template));
				fclose ($fd);
		
			} else {
				print('
					<div class="tplErrorMessage">
						Das File <span class="fileName">'. $template .'</span> existiert nicht oder kann nicht gelesen werden!
					</div>'."\n"
				);
				exit;
			}
		}

		// nach objecten parsen
		//$this->objectFindTokens();
	}


    /**
     * This method assings a given content string to a token. The token will replaced by this content string.
     *
     * @param {string} $content	the content string for replacing the $token
     * @param {string} $token	which is the inner part of a template text token like:
     *                          {REPLACE_THIS} - REPLACE_THIS will be replaced by the content of $content
     *
     * @return {string} the current template where the $token ist replaced by $content
     */
	function replaceTokenByContent( $token, $content) {
		$template = $this->_tpl_out;												// buffered template uebergeben
		$template = eregi_replace("{". $token ."}", "$content", "$template");		// token ersetzen
		$this->_tpl_out = $template;												// und template zurueck
	}

	// einen platzhalter loeschen
	function deleteToken( $token ) {
		$template = $this->_tpl_out;												// buffered template uebergeben
		$template = str_ireplace("{". $token ."}", "", $template);
		$this->_tpl_out = $template;												// und template zurueck
	}

	// aktuelles template anzeigen
	function show() {
		echo $this->_tpl_out;
		flush();
	}

	// aktuelles template zurueckgeben
	function get() {
		return $this->_tpl_out;
	}

	function init() {
		$this->_tpl_name = "";
		$this->_tpl_out = "";
	}
}



class TemplateFromFile extends Template {
	// konstruktor //
	public function TemplateFromFile($filename) {
		parent::Template($filename, TRUE);
	}
}



class TemplateFromString extends Template {
	// konstruktor //
	public function TemplateFromString($filename) {
		parent::Template($filename, FALSE);
	}
}
?>
