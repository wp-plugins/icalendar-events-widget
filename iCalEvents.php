<?php
/*
	Plugin Name: iCalendar Events Widget
	Plugin Script: iCalEvents.php
	Plugin URI: http://programmschmie.de/software/web/iCalEvents/
	Description: Shows you upcoming events for a configurable iCalendar .ics file.
	Version: v0.1b
	Author: [programmschmie.de]
	Author URI: http://programmschmie.de
	Text Domain: icalevents
	Domain Path: /languages
	
	$Id$
*/
/* 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
	Online: http://www.gnu.org/licenses/gpl.txt
*/


define('ICALEVENTS_VERSION', '0.1');

class iCalEvents extends WP_Widget {
	public $newline = "\n";
	
	private	/** @type {string} */ $widgetFilePath;
	private /** @type {string} */ $libPath;
	private /** @type {string} */ $templatePath;
	private /** @type {string} */ $languagePath;
    
    
    function iCalEvents() {
		$this->widgetFilePath	= dirname(__FILE__);
		$this->libPath			= $this->widgetFilePath.'/lib/';
		$this->templatePath		= $this->widgetFilePath.'/templates/';
		$this->languagePath		= $this->widgetFilePath.'/languages/';
	    
        load_plugin_textdomain('icalevents', 'false', $this->languagePath);
    
	    if (!file_exists( $this->libPath.'class.iCalReader.php' )) return false;
	    require_once( $this->libPath.'class.iCalReader.php' );
		
	    if (!file_exists( $this->libPath.'class.Template.php' )) return false;
	    require_once( $this->libPath.'class.Template.php' );


	    $css_url = plugins_url(basename(dirname(__FILE__)) . '/icalendar.css');
	    wp_register_style('iCalEvents', $css_url, array(), ICALEVENTS_VERSION, 'screen');
	    wp_enqueue_style('iCalEvents');

		$widget_ops = array('classname' => __CLASS__, 'description' => __('Shows you upcoming events for a configurable iCalendar .ics file.', 'icalevents'));
		parent::WP_Widget(__CLASS__, 'iCalendar Events', $widget_ops);
    }

    function form( $instance ) {
        // setting up stored values for all widget options
        if ( $instance ) {
			$title					= esc_attr( $instance[ 'title' ] );
			$iCalURI				= esc_attr( $instance[ 'iCalURI' ] );
			$showNrOfEvents			= esc_attr( $instance[ 'showNrOfEvents' ] );
			$showEventDateStart		= esc_attr( $instance[ 'showEventDateStart' ] );
			$showEventDateEnd		= esc_attr( $instance[ 'showEventDateEnd' ] );
			$showEventTimeStart		= esc_attr( $instance[ 'showEventTimeStart' ] );
			$showEventTimeEnd		= esc_attr( $instance[ 'showEventTimeEnd' ] );
			$showEventSummary		= esc_attr( $instance[ 'showEventSummary' ] );
			$showEventDescription	= esc_attr( $instance[ 'showEventDescription' ] );
			$showEventLocation		= esc_attr( $instance[ 'showEventLocation' ] );
			$showEventURL			= esc_attr( $instance[ 'showEventURL' ] );
			
		// setting up default values for all widget options
		} else {
			$title					= __( 'iCalendar Events', 'icalevents' );
			$iCalURI				= '';
			$showNrOfEvents			= '5';			// show the next 5 events by default
			$showEventDateStart		= true;			// 0/false = no, 1/true = yes
			$showEventDateEnd		= false;		// 0/false = no, 1/true = yes
			$showEventTimeStart		= false;		// 0/false = no, 1/true = yes
			$showEventTimeEnd		= false;		// 0/false = no, 1/true = yes
			$showEventSummary		= true;			// 0/false = no, 1/true = yes
			$showEventDescription	= true;			// 0/false = no, 1/true = yes
			$showEventLocation		= true;			// 0/false = no, 1/true = yes
			$showEventURL			= false;		// 0/false = no, 1/true = yes
		}
		
		// build an asoc array with all of our options as items
		$widgetOptions = array(
			array(
				'field_id'		=> 'title',
				'field_name'	=> 'title',
				'field_label'	=> __( 'Title', 'icalevents' ),
				'field_title'	=> __( 'If you leave this setting empty, this widget will show the calendar name in your sidebar box title', 'icalevents' ),
				'field_type'	=> 'text',
				'field_value'	=> $title
			),
			array(
				'field_id'		=> 'iCalURI',
				'field_name'	=> 'iCalURI',
				'field_label'	=> __( 'iCalendar Subscription URL', 'icalevents' ),
				'field_title'	=> __( 'Enter a full qualified URL to a iCalendar subscription. It *MUST* start with \'http://\' or \'webcal://\'. ', 'icalevents' ),
				'field_type'	=> 'text',
				'field_value'	=> $iCalURI
			),
			array(
				'field_id'		=> 'showNrOfEvents',
				'field_name'	=> 'showNrOfEvents',
				'field_label'	=> __( 'Show # of upcoming events', 'icalevents' ),
				'field_title'	=> __( 'Enter a numeric value here. It represents the number of the events that will be shown in your sidebar box.', 'icalevents' ),
				'field_type'	=> 'text',
				'field_value'	=> $showNrOfEvents
			),
			array(
				'field_id'		=> 'showEventSummary',
				'field_name'	=> 'showEventSummary',
				'field_label'	=> __( 'Show event summary', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_value'	=> $showEventSummary
			),
			array(
				'field_id'		=> 'showEventDateStart',
				'field_name'	=> 'showEventDateStart',
				'field_label'	=> __( 'Show start date', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_value'	=> $showEventDateStart
			),
			array(
				'field_id'		=> 'showEventDateEnd',
				'field_name'	=> 'showEventDateEnd',
				'field_label'	=> __( 'Show end date', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_value'	=> $showEventDateEnd
			),
			array(
				'field_id'		=> 'showEventTimeStart',
				'field_name'	=> 'showEventTimeStart',
				'field_label'	=> __( 'Show start time', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_value'	=> $showEventTimeStart
			),
			array(
				'field_id'		=> 'showEventTimeEnd',
				'field_name'	=> 'showEventTimeEnd',
				'field_label'	=> __( 'Show end time', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_value'	=> $showEventTimeEnd
			),
			array(
				'field_id'		=> 'showEventDescription',
				'field_name'	=> 'showEventDescription',
				'field_label'	=> __( 'Show description', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_value'	=> $showEventDescription
			),
			array(
				'field_id'		=> 'showEventLocation',
				'field_name'	=> 'showEventLocation',
				'field_label'	=> __( 'Show location', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_value'	=> $showEventLocation
			),
			array(
				'field_id'		=> 'showEventURL',
				'field_name'	=> 'showEventURL',
				'field_label'	=> __( 'Show a given URL', 'icalevents' ),
				'field_title'	=> __( '', 'icalevents' ),
				'field_type'	=> 'checkbox',
				'field_value'	=> $showEventURL
			),
		);
		
		$closePTag = true;
		foreach($widgetOptions as $option) {
			switch ($option['field_type']) {
				case 'checkbox':
					$checkBoxTemplate = new TemplateFromFile( $this->templatePath.'admin_option_checkbox.tpl' );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_ID',				$this->get_field_id( $option['field_id'] ) );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_NAME',			$this->get_field_name( $option['field_name'] ) );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_VALUE',			$option['field_value'] );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_LABEL',			$option['field_label'] );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_LABEL',			$option['field_title'] );
					$checkBoxTemplate->replaceTokenByContent( 'OPTION_ITEM_CHECKED_FLAG',	($option['field_value'] ? 'checked="checked"' : '') );

					if ($closePTag) print('<p>');
					$checkBoxTemplate->show();
					$closePTag = false;
				break;
				
				case 'text':
				default:
					$textFieldTemplate = new TemplateFromFile( $this->templatePath.'admin_option_textfield.tpl' );
					$textFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_ID',		$this->get_field_id( $option['field_id'] ) );
					$textFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_NAME',		$this->get_field_name( $option['field_name'] ) );
					$textFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_VALUE',		$option['field_value'] );
					$textFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_LABEL',		$option['field_label'] );
					$textFieldTemplate->replaceTokenByContent( 'OPTION_ITEM_TITLE',		$option['field_title'] );

					if (!$closePTag) print('</p>');		// switch from checkbox to textfield
					print('<p>');
					$textFieldTemplate->show();
					$closePTag = true;
				break;
			}
			if ($closePTag) print('</p>');				// if the last option in loop was a textfield
		}
		if (!$closePTag) print('</p>');					// if the last option was a checkbox field
    }


    function update( $new_instance, $old_instance ) {
        // processes widget options to be saved
        $instance = $old_instance;
		$instance['title']					= strip_tags($new_instance['title']);
		$instance['iCalURI']				= strip_tags($new_instance['iCalURI']);
		$instance['showNrOfEvents']			= strip_tags($new_instance['showNrOfEvents']);
		$instance['showEventDateStart']		= strip_tags($new_instance['showEventDateStart']);
		$instance['showEventDateEnd']		= strip_tags($new_instance['showEventDateEnd']);
		$instance['showEventTimeStart']		= strip_tags($new_instance['showEventTimeStart']);
		$instance['showEventTimeEnd']		= strip_tags($new_instance['showEventTimeEnd']);
		$instance['showEventSummary']		= strip_tags($new_instance['showEventSummary']);
		$instance['showEventDescription']	= strip_tags($new_instance['showEventDescription']);
		$instance['showEventLocation']		= strip_tags($new_instance['showEventLocation']);
		$instance['showEventURL']			= strip_tags($new_instance['showEventURL']);
		return $instance;
    }


    function widget( $args, $instance ) {
        // outputs the content of the widget
        // ADD YOUR FRONT-END FORM HERE
        extract( $args );
		$title					= trim(apply_filters( 'widget_title', $instance['title'] ));
		$iCalURI				= trim($instance['iCalURI']);
		$showNrOfEvents			= $instance['showNrOfEvents'];
		$showEventDateStart		= $instance['showEventDateStart'];
		$showEventDateEnd		= $instance['showEventDateEnd'];
		$showEventTimeStart		= $instance['showEventTimeStart'];
		$showEventTimeEnd		= $instance['showEventTimeEnd'];
		$showEventSummary		= stripslashes_deep($instance['showEventSummary']);
		$showEventDescription	= stripslashes_deep($instance['showEventDescription']);
		$showEventLocation		= stripslashes_deep($instance['showEventLocation']);
		$showEventURL			= stripslashes_deep($instance['showEventURL']);
		
		// prepare the URI for use with the "ical" class
		$iCalURI = str_ireplace ( 'webcal:' , 'http:' , $instance['iCalURI'] );

		$iCal = new ical($iCalURI);
		$currentDate = new DateTime('2038/12/31');
		$iCalEvents = $iCal->events();

		// if the title for this event list is not given
		// by the options, so set the widget title to iCal name
		$title = ( $title == '' ? $iCal->cal['VCALENDAR']['X-WR-CALNAME'] : $title);


		// -----------------------------------------------------------------------------------------------
		// show the widget
		// -----------------------------------------------------------------------------------------------
		
		print( $before_widget );
		if ( $title )
			print( $before_title . $title . $after_title );
		
		// there are NO event items
		if (count($iCalEvents) == 0) {
			$eventListEmptyTpl = new TemplateFromFile( $this->templatePath.'event_list_empty.tpl' );
			$eventListEmptyTpl->replaceTokenByContent( 'EVENTLIST_EMPTY_MESSAGE', __( 'There are no events available.', 'iCalEvents' ) );
			$eventListEmptyTpl->show();
		

		// there are event items available
		} else {
			$eventCounter = 0;
			$eventListItems = '';

			// iterate through the events, extract the $showNrOfEvents events
			// and put into a list
			foreach( $iCalEvents as $anEvent) {
				// get a new event list item template
				$eventListItemTpl = new TemplateFromFile( $this->templatePath.'event_list_item.tpl' );

				// ---------------------------------------------------------------------------------------
				// event start date
				// ---------------------------------------------------------------------------------------
				if ($showEventDateStart) {
					$eventDateStartTpl = new TemplateFromString( '<span class="eventListItemDateStart">{EVENTLIST_ITEM_DATE_START}</span>' );
					$eventDateStartTpl->replaceTokenByContent( 'EVENTLIST_ITEM_DATE_START', date('d.m.Y', $iCal->iCalDateToUnixTimestamp($anEvent['DTSTART'])) );
					$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_DATE_START_TPL', $eventDateStartTpl->get() );
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_DATE_START_TPL' );
				}

				// ---------------------------------------------------------------------------------------
				// event start time
				// ---------------------------------------------------------------------------------------
				if ($showEventTimeStart) {
					$eventTimeStartTpl = new TemplateFromString( '<span class="eventListItemTimeStart">{EVENTLIST_ITEM_TIME_START}</span>' );
					$eventTimeStartTpl->replaceTokenByContent( 'EVENTLIST_ITEM_TIME_START', date('H:i', $iCal->iCalDateToUnixTimestamp($anEvent['DTSTART'])) );
					$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_TIME_START_TPL', $eventTimeStartTpl->get() );
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_TIME_START_TPL' );
				}

				// ---------------------------------------------------------------------------------------
				// event end date
				// ---------------------------------------------------------------------------------------
				if ($showEventDateEnd) {
					$eventDateEndTpl = new TemplateFromString( '<span class="eventListItemDateEnd">{EVENTLIST_ITEM_DATE_END}</span>' );
					$eventDateEndTpl->replaceTokenByContent( 'EVENTLIST_ITEM_DATE_END', date('d.m.Y', $iCal->iCalDateToUnixTimestamp($anEvent['DTEND'])) );
					$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_DATE_END_TPL', $eventDateEndTpl->get() );
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_DATE_END_TPL' );
				}

				// ---------------------------------------------------------------------------------------
				// event end time
				// ---------------------------------------------------------------------------------------
				if ($showEventTimeEnd) {
					$eventTimeEndTpl = new TemplateFromString( '<span class="eventListItemTimeEnd">{EVENTLIST_ITEM_TIME_END}</span>' );
					$eventTimeEndTpl->replaceTokenByContent( 'EVENTLIST_ITEM_TIME_END', date('H:i', $iCal->iCalDateToUnixTimestamp($anEvent['DTEND'])) );
					$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_TIME_END_TPL', $eventTimeEndTpl->get() );
				}
				else						$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_TIME_END_TPL' );
				
				// ---------------------------------------------------------------------------------------
				// event summary
				// ---------------------------------------------------------------------------------------
				if (array_key_exists( 'SUMMARY', $anEvent )) {
					if ($showEventSummary) {
						$eventSummaryTpl = new TemplateFromString( '<span class="eventListItemSummary">{EVENTLIST_ITEM_SUMMARY}</span>' );
						$eventSummaryTpl->replaceTokenByContent( 'EVENTLIST_ITEM_SUMMARY', $anEvent['SUMMARY'] );
						$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_SUMMARY_TPL', $eventSummaryTpl->get() );
					} else {
						$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_SUMMARY_TPL' );
					}
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_SUMMARY_TPL' );
				}

				// ---------------------------------------------------------------------------------------
				// event description
				// ---------------------------------------------------------------------------------------
				if (array_key_exists( 'DESCRIPTION', $anEvent )) {
					if ($showEventDescription) {
						$eventDescriptionTpl = new TemplateFromString( '<span class="eventListItemDescription">{EVENTLIST_ITEM_DESCRIPTION}</span>' );
						$eventDescriptionTpl->replaceTokenByContent( 'EVENTLIST_ITEM_DESCRIPTION', $anEvent['DESCRIPTION'] );
						$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_DESCRIPTION_TPL', $eventDescriptionTpl->get() );
					} else {
						$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_DESCRIPTION_TPL' );
					}
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_DESCRIPTION_TPL' );
				}

				// ---------------------------------------------------------------------------------------
				// event location
				// ---------------------------------------------------------------------------------------
				if (array_key_exists( 'LOCATION', $anEvent )) {
					if ($showEventLocation) {
						$eventLocationTpl = new TemplateFromString( '<span class="eventListItemLocation">{EVENTLIST_ITEM_LOCATION}</span>' );
						$eventLocationTpl->replaceTokenByContent( 'EVENTLIST_ITEM_LOCATION', $anEvent['LOCATION'] );
						$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_LOCATION_TPL', $eventLocationTpl->get() );
					} else {
						$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_LOCATION_TPL' );
					}
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_LOCATION_TPL' );
				}

				// ---------------------------------------------------------------------------------------
				// event url
				// ---------------------------------------------------------------------------------------
				if (array_key_exists( 'URL;VALUE=URI', $anEvent )) {
					if ($showEventURL) {
						$eventURLTpl = new TemplateFromString( '<span class="eventListItemURL"><a href="{EVENTLIST_ITEM_URL}" target="_blank">{EVENTLIST_ITEM_URL}</a></span>' );
						$eventURLTpl->replaceTokenByContent( 'EVENTLIST_ITEM_URL', $anEvent['URL;VALUE=URI'] );
						$eventListItemTpl->replaceTokenByContent( 'EVENTLIST_ITEM_URL_TPL', $eventURLTpl->get() );
					} else {
						$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_URL_TPL' );
					}
				} else {
					$eventListItemTpl->deleteToken( 'EVENTLIST_ITEM_URL_TPL' );
				}

				// append the string of the current item to the list of items
				$eventListItems .= $eventListItemTpl->get();
				$eventListItemTpl = NULL;
				
				$eventCounter++;
				if ($eventCounter >= (int)$showNrOfEvents)
					break;
			}
			
			// replace the event list item token by real content
			$eventListTpl = new TemplateFromFile( $this->templatePath.'event_list.tpl' );
			$eventListTpl->replaceTokenByContent( 'EVENTLIST_ITEMS',  $eventListItems);
			$eventListTpl->show();
			$eventListItems = '';
		}
		print( $after_widget );
    }
}
add_action('widgets_init', create_function('', "register_widget('iCalEvents');"));

?>