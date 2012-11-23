<?php /* $Id$ $URL$ */
/*
Dayplanner v1.0.0
Klaus Buecher
   

LICENSE

=====================================

The Dayplanner module was built by Klaus Buecher and is released here
under modified BSD license (see GNU.org).

Copyright (c) 2012 Klaus Buecher (Opto)

*/
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $this_day, $first_time, $last_time, $company_id, $event_filter, $event_filter_list, $AppUI;

// load the event types
$types = w2PgetSysVal('EventType');
$links = array();

$perms = &$AppUI->acl();
$user_id = $AppUI->user_id;
$other_users = false;
$no_modify = false;

if (canView('admin')) {
	$other_users = true;
	if (($show_uid = w2PgetParam($_REQUEST, 'show_user_events', 0)) != 0) {
		$user_id = $show_uid;
		$no_modify = true;
		$AppUI->setState('event_user_id', $user_id);
	}
}
// assemble the links for the events
$events = CEvent::getEventsForPeriod($first_time, $last_time, $event_filter, $user_id);
$events2 = array();

$start_hour = w2PgetConfig('cal_day_start');
$end_hour = w2PgetConfig('cal_day_end');
foreach ($events as $row) {
    $start = new w2p_Utilities_Date($row['event_start_date']);
	$end = new w2p_Utilities_Date($row['event_end_date']);
	$events2[$start->format('%H%M%S')][] = $row;

	if ($start_hour > $start->format('%H')) {
		$start_hour = $start->format('%H');
	}
	if ($end_hour < $end->format('%H')) {
		$end_hour = $end->format('%H');
	}
}

$tf = $AppUI->getPref('TIMEFORMAT');

$dayStamp = $this_day->format(FMT_TIMESTAMP_DATE);

$start = $start_hour;
$end = $end_hour;
$inc = w2PgetConfig('cal_day_increment');

if ($start === null)
	$start = 8;
if ($end === null)
	$end = 17;
if ($inc === null)
	$inc = 15;

$this_day->setTime($start, 0, 0);

$html = '
<form action="'.$_SERVER['REQUEST_URI'].'" method="post" name="pickFilter" accept-charset="utf-8">';
$html .= $AppUI->_('Event Filter') . ':' . arraySelect($event_filter_list, 'event_filter', 'onChange="document.pickFilter.submit()" class="text"', $event_filter, true);
if ($other_users) {
	$html .= $AppUI->_('Show Events for') . ':' . '<select name="show_user_events" onchange="document.pickFilter.submit()" class="text">';

	if (($rows = w2PgetUsersList())) {
		foreach ($rows as $row) {
			if ($user_id == $row['user_id'])
				$html .= '<option value="' . $row['user_id'] . '" selected="selected">' . $row['contact_first_name'] . ' ' . $row['contact_last_name'];
			else
				$html .= '<option value="' . $row['user_id'] . '">' . $row['contact_first_name'] . ' ' . $row['contact_last_name'];
		}
	}
	$html .= '</select>';

}

require_once (W2P_BASE_DIR . '/modules/calendar/links_events.php');
//<script type='text/javascript' src='../jquery/jquery-1.8.1.min.js'></script>

$html .= '</form>';

$html.='<br>';
$html .="<link rel='stylesheet' type='text/css' href='./modules/calendar/fullcalendar/fullcalendar.css' />
";
$html .="<link rel='stylesheet' type='text/css' href='./modules/calendar/fullcalendar/fullcalendar.print.css' media='print' />";
/*$html .="<script type='text/javascript' src='../jquery/jquery-ui-1.8.23.custom.min.js'></script>";
$html .="<script type='text/javascript' src='../fullcalendar/fullcalendar.min.js'></script>";
*/




$html.="<script type='text/javascript'>

	$(document).ready(function() {
	
		var date = new Date();
		var d = date.getDate();
		var m = date.getMonth();
		var y = date.getFullYear();
	/* initialize the external events
		-----------------------------------------------------------------*/
	
		$('#external-events div.external-event').each(function() {
		
			// create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
			// it doesn't need to have a start or end
			var eventObject = {
				title: $.trim($(this).text()) // use the element's text as the event title
			};
			
			// store the Event Object in the DOM element so we can get to it later
			$(this).data('eventObject', eventObject);
			
			// make the event draggable using jQuery UI
			$(this).draggable({
				zIndex: 999,
				revert: true,      // will cause the event to go back to its
				revertDuration: 0  //  original position after the drag
			});
			
		});
	
		
		$('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			defaultView: 'agendaDay',
			editable: true,
                        slotMinutes:15,
eventRender: function(event, element) {                                          
	element.find('span.fc-event-title').html(element.find('span.fc-event-title').text());					  
},
			events: [
				{
					title: 'All Day Event',
					start: new Date(y, m, 1)
				},
				{
					title: ' ARD Tagesschau',
					start: new Date(y, m, d-5),
					end: new Date(y, m, d-2)
				},
				{
					id: 999,
					title: 'Repeating Event',
					start: new Date(y, m, d-3, 16, 0),
					allDay: false
				},
				{
					id: 999,
					title: 'Repeating Event',
					start: new Date(y, m, d+4, 16, 0),
					allDay: false
				},
				{
					title: 'Meeting',
					start: new Date(y, m, d, 10, 30),
					allDay: false
				},
				{
					title: 'Lunch',
					start: new Date(y, m, d, 12, 0),
					end: new Date(y, m, d, 14, 0),
					allDay: false
				},
				{
					title: 'Birthday Party',
					start: new Date(y, m, d+1, 19, 0),
					end: new Date(y, m, d+1, 22, 30),
					allDay: false
				},
				{
					title: 'Click for Google',
					start: new Date(y, m, 28),
					end: new Date(y, m, 29),
					url: 'http://google.com/'
				}
			],

			droppable: true, // this allows things to be dropped onto the calendar !!!

			drop: function(date, allDay) { // this function is called when something is dropped
			
				// retrieve the dropped element's stored Event Object
				var originalEventObject = $(this).data('eventObject');
				
				// we need to copy it, so that multiple events don't have a reference to the same object
				var copiedEventObject = $.extend({}, originalEventObject);
				
				// assign it the date that was reported
				copiedEventObject.start = date;
				copiedEventObject.allDay = allDay;
				
				// render the event on the calendar
				// the last `true` argument determines if the event sticks (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
				$('#calendar').fullCalendar('renderEvent', copiedEventObject, true);
				
				// is the remove after drop checkbox checked?
				if ($('#drop-remove').is(':checked')) {
					// if so, remove the element from the Draggable Events list
					$(this).remove();
				}
				
			}

});
		
	});

</script>";





/*

$html .= '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';
$rows = 0;
for ($i = 0, $n = ($end - $start) * 60 / $inc; $i < $n; $i++) {
	$html .= '<tr>';

	$tm = $this_day->format($tf);
	$html .= '<td width="1%" align="right" nowrap="nowrap">' . ($this_day->getMinute() ? $tm : '<b>' . $tm . '</b>') . '</td>';

	$timeStamp = $this_day->format('%H%M%S');
	if (isset($events2[$timeStamp])) {
		$count = count($events2[$timeStamp]);
		for ($j = 0; $j < $count; $j++) {
			$row = $events2[$timeStamp][$j];

			$et = new w2p_Utilities_Date($row['event_end_date']);
			$rows = (($et->getHour() * 60 + $et->getMinute()) - ($this_day->getHour() * 60 + $this_day->getMinute())) / $inc;

			$href = '?m=calendar&a=view&event_id=' . $row['event_id'];
			$alt = $row['event_description'];

			$html .= '<td class="event" rowspan="' . $rows . '" valign="top">';

			$html .= '<table cellspacing="0" cellpadding="0" border="0"><tr>';
			$html .= '<td>' . w2PshowImage('event' . $row['event_type'] . '.png', 16, 16, '', '', 'calendar');
			$html .= '</td><td>&nbsp;<b>' . $AppUI->_($types[$row['event_type']]) . '</b></td></tr></table>';
			$html .= w2PtoolTip($row['event_name'], getEventTooltip($row['event_id']), true);
			$html .= $href ? '<a href="' . $href . '" class="event">' : '';
			$html .= $row['event_name'];
			$html .= $href ? '</a>' : '';
			$html .= w2PendTip();
			$html .= '</td>';
		}
	} else {
		if (--$rows <= 0) {
			$html .= '<td></td>';
		}
	}

	$html .= '</tr>';

	$this_day->addSeconds(60 * $inc);
}

$html .= '</table>';  */
/*
 * $html.="<style type='text/css'>

	body {
		margin-top: 40px;
		text-align: center;
		font-size: 14px;
		font-family: 'Lucida Grande',Helvetica,Arial,Verdana,sans-serif;
		}

	#calendar {
		width: 900px;
		margin: 0 auto;
		}

</style>";
*/
$html.="<style type='text/css'>

	body {
		margin-top: 40px;
		text-align: center;
		font-size: 14px;
		}
		
	#wrap {
		width: 1100px;
		margin: 0 auto;
		}
		
	#external-events {
		float: left;
		width: 150px;
		padding: 0 10px;
		border: 1px solid #ccc;
		background: #eee;
		text-align: left;
		}
		
	#external-events h4 {
		font-size: 16px;
		margin-top: 0;
		padding-top: 1em;
		}
		
	.external-event { /* try to mimick the look of a real event */
		margin: 10px 0;
		padding: 2px 4px;
		background: #3366CC;
		color: #fff;
		font-size: .85em;
		cursor: pointer;
		}
		
	#external-events p {
		margin: 1.5em 0;
		font-size: 11px;
		color: #666;
		}
		
	#external-events p input {
		margin: 0;
		vertical-align: middle;
		}

	#calendar {
		float: right;
		width: 900px;
		}

</style>";
//$html.="<div id='calendar'></div>";
$html.="<div id='wrap'>

<div id='external-events'>
<h4>Draggable Events</h4>
<div class='external-event'>My Event 1</div>
<div class='external-event'>My Event 2</div>
<div class='external-event'>My Event 3</div>
<div class='external-event'>My Event 4</div>
<div class='external-event'>My Event 5</div>
<p>
<input type='checkbox' id='drop-remove' /> <label for='drop-remove'>remove after drop</label>
</p>
</div>

<div id='calendar'></div>

<div style='clear:both'></div>
</div>
";
echo $html;