<?php
/**
 * Creates month, week, day and list calendar views. The output can be heavily
 * customized through a large set of options.
 *
 * @See SimpleCalendarEvent, SimpleCalendarView
 */

/* Copyright © 2014 Arne Ludwig <arne.ludwig@posteo.de>
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program. If
 * not, see <http://www.gnu.org/licenses/>.
 */

if(! class_exists('SimpleCalendarEvent')) {
  /**
   * Simple data object for events. Additionally, it provides some convenience methods.
   */
  class SimpleCalendarEvent {
    private static $defaultOptions = array(
      'title' => '',
      'isAllDay' => false,
      'venue' => '',
      'description' => '',
      'category' => '',
      'color' => '',
      'anchorAttrs' => array()
    );
    
    protected $options;
    protected $startOfDay;
    
    /**
     * Constructs a new object from either an SimpleCalendarEvent or from $options with the following
     * fields.
     *
     * * __title__ (default: ''): event title
     * * __start__ (required): event start including date and time given as DateTime or date string
     * * __end__ (required): event end including date and time given as DateTime or date string
     * * __isAllDay__ (default: false): indicates whether the event is all day
     * * __venue__ (default: ''): event venue, e.g. `Berlin, Central Station`
     * * __description__ (default: ''): event description
     * * __category__ (default: ''): event category, e.g. `Private`
     * * __color__ (default: ''): event color in CSS-ready format, e.g. `#ff0000`
     * * __anchorAttrs__ (default: array()): array of key-value-pairs for each HTML attribute of
     *   the anchor element
     *
     * __Example:__
     *
     *     array(
     *       'title' => 'My Event',
     *       'isAllDay' => false,
     *       'venue' => 'New York, Central Park',
     *       'description' => 'Huge event in Central Park',
     *       'category' => 'Public Meetings',
     *       'color' => 'red',
     *       'anchorAttrs' => array('href' => 'http://www.example.org/')
     *     );
     */
    public function __construct($options) {
      if($options instanceof SimpleCalendarEvent) {
        $this->options = $options->options;
        $this->startOfDay = $options->startOfDay;
      } else {
        $this->options = array_merge(self::$defaultOptions, $options);
        
        if(! isset($this->options['start']))
          self::missingOption('start');
        if(! isset($this->options['end']))
          self::missingOption('end');
        
        $this->options['start'] = new SimpleDateTime($this->options['start']);
        $this->options['end'] = new SimpleDateTime($this->options['end']);
          
        $this->startOfDay = $this->start->copy();
        $this->startOfDay->setTime(0, 0, 0);
      }
    }
    
    private static function missingOption($name) {
      trigger_error("Missing option '$name'", E_USER_WARNING);
    }
    
    public function __get($option) {
      if(isset($this->options[$option])) {
        return $this->options[$option];
      } else {
        trigger_error("Undefined property '$option'", E_USER_NOTICE);
        return null;
      }
    }
    
    public function __set($option, $value) {
      $this->options[$option] = $value;
    }
    
    public function __isset($option) {
      return isset($this->options[$option]);
    }
    
    public function startPercentageOfDay() {
      $dayTimestamp = $this->startOfDay->getTimestamp();
      $startTimestamp = $this->start->getTimestamp();
      
      return self::percentageOfDay($startTimestamp - $dayTimestamp);
    }
    
    public function durationPercentageOfDay() {
      $startTimestamp = $this->start->getTimestamp();
      $endTimestamp = $this->end->getTimestamp();

      return self::percentageOfDay($endTimestamp - $startTimestamp);
    }
    
    const secondsPerDay = 86400;
    protected static function percentageOfDay($seconds) {
      return 100 * $seconds / self::secondsPerDay;
    }
  }

  /**
   * The parent class of each view. Further, it provides easy access to the
   * current dates attributes.
   *
   * @uses $wp_locale
   */
  abstract class SimpleCalendarView extends SimpleHTMLBuilder {
    // TODO replace *Class by *Attrs and expected the same as $htmlBuiler->open does
    private static $defaultOptions = array(
      'allDayEventListClass' => 'event-list all-day',
      'allDayEventTemplate' => '%title',
      'allDayRowClass' => 'all-day-row',
      'allDayTimeColumnClass' => 'all-day-time-column',
      'contentRowClass' => 'content-row',
      'dateFormat' => 'l, j. F Y',
      'dayHeadingClass' => 'day-heading',
      'dayListAttrs' => array('class' => 'simple-calendar-view'),
      'dayNamesFilter' => null,
      'dayNamesTemplate' => '',
      'dayNumberClass' => 'day-number',
      'dayNumbersFilter' => null,
      'defaultColor' => '#ddd',
      'emptyValue' => '&nbsp;',
      'evenRowClass' => 'even',
      'eventBoxClass' => 'event-box',
      'eventCellClass' => 'event-cell',
      'eventCellFilter' => null,
      'eventColumnClass' => 'event-column',
      'eventFilter' => null,
      'eventFrameClass' => 'event-frame',
      'eventListClass' => 'event-list',
      'eventListItemClass' => 'event-list-item',
      'eventTemplate' => '',
      'generateEvents' => null,
      'headerCellClass' => 'header-cell',
      'headerFilter' => null,
      'headerRowClass' => 'header-row',
      'maxDays' => 14,
      'maxEvents' => 20,
      'monthDayClass' => 'month-day',
      'nonMonthDayClass' => 'non-month-day',
      'oddRowClass' => 'odd',
      'tableAttrs' => array('class' => 'simple-calendar-view'),
      'timeCellClass' => 'time-cell',
      'timeCellIDPrefix' => 'hour-',
      'timeColumnClass' => 'time-column',
      'timeColumnHeaderClass' => 'time-column-header',
      'timeContainerClass' => 'time-container',
      'timeFilter' => null,
      'timeFormat' => 'G:i',
      'todayClass' => 'today',
      'weekdayClasses' => array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'),
      'weekStart' => 0
    );
    
    protected $date;
      
    public function __construct($date, $options, $defaultOptions) {
      $local_options = array_merge(self::$defaultOptions, $defaultOptions, $options);
      parent::__construct($local_options);
      $this->now = new SimpleDateTime('now', $date->getTimezone());
      $this->date = new SimpleDateTime($date);
      $this->date->setTime(0, 0, 0);
    }

    abstract public function nextDate();
    abstract public function previousDate();

    protected function tryFilter($callbackName, &$inOut) {
      $args = func_get_args();
      array_shift($args); // remove $callbackName from array
      // always provides the filter input ($inOut)
      
      if($this->getOption($callbackName))
        $inOut = call_user_func_array($this->getOption($callbackName), $args);
    }

    protected function getTemplate($source) {
      if(! is_callable($source)) {
        return $source;
      } else {
        $args = func_get_args();
        array_shift($args); // remove $source from array
        return call_user_func_array($source, $args);
      }
    }
    
    protected function isToday($date=null) {
      if(! isset($date))
        $date = $this->date;
      return $date->format('Y-m-d') == $this->now->format('Y-m-d');
    }
    
    protected function dayNamesTemplate($day, $date) {
      $dayNamesTemplate = self::getTemplate($this->getOption('dayNamesTemplate'), $date->copy());

      if(is_array($dayNamesTemplate)) {
        return $dayNamesTemplate[$day];
      } else {
        global $wp_locale;
        
        $dayName = $wp_locale->get_weekday($day);
        return self::evalTemplateString(
          $dayNamesTemplate,
          (object) array(
            'full' => $dayName,
            'abbrev' => $wp_locale->get_weekday_abbrev($dayName),
            'initial' => $wp_locale->get_weekday_initial($dayName),
            'date' => $date
          )
        );
      }
    }
    
    protected function weekStart() {
      $dayOfWeek = ($this->date->format('w') - $this->getOption('weekStart') + 7) % 7;
      return $this->date->copy()->modify('-' . $dayOfWeek . ' days');
    }

    protected function &insertEventList($isAllDay) {
      $this->ul($this->optionsClass($isAllDay ? 'allDayEventListClass' : 'eventListClass'));
      foreach($this->events as $event) {
        self::tryFilter('eventFilter', $event, $isAllDay);
        
        if(! $event || ! (is_array($event) || $event instanceof SimpleCalendarEvent)) {
          $this->append($event);
        } else {
          $event = new SimpleCalendarEvent($event);
          if($isAllDay == $event->isAllDay)
            $this->eventFilter($event, $isAllDay);
        }
      }
      
      return $this->close('ul');
    }

    protected function generateEvents($date, $isNonMonthDay=false) {
      $this->events = call_user_func(
        $this->getOption('generateEvents'),
        $date->copy(),
        $isNonMonthDay
      );
    }
    
    protected function &eventFilter($event, $isAllDay) {
      $eventBoxAttrs = array(
        'class' => $this->getOption('eventBoxClass'),
        'style' => sprintf(
          'top: %1$f%%; min-height: %2$f%%; color: %3$s; background-color: %3$s; border-color: %3$s;',
          max($event->startPercentageOfDay(), 0.0),
          min($event->durationPercentageOfDay(), 100.0 - $event->startPercentageOfDay()),
          $event->color ? $event->color : $this->getOption('defaultColor')
        )
      );
      $eventTemplate = self::getTemplate(
        $this->getOption($event->isAllDay ? 'allDayEventTemplate' : 'eventTemplate'),
        $event,
        $isAllDay
      );
      $templatedContent = self::evalTemplateString($eventTemplate, $event);
      
      return $this->
        li($this->optionsClass($isAllDay ? 'allDayEventListItemClass' : 'eventListItemClass'))->
          a($event->anchorAttrs)->
            div($eventBoxAttrs)->
              append($templatedContent)->
            close('div')->
          close('a')->
        close('li');
    }
    
    protected function optionsAppended($field, $extension=array()) {
      $options = $this->getOption($field);
      foreach($extension as $extField => $extValue)
        $options[$extField] .= ' ' . $extValue;
    }
    
    protected function optionsClass($optionsField, $moreClasses=null) {
      return array('class' =>
        $this->getOption($optionsField) . (isset($moreClasses) ? ' ' . $moreClasses : '')
      );
    }
  }

  /**
   * The parent class of table-based views.
   */
  abstract class SimpleCalendarTableBasedView extends SimpleCalendarView {
    public function __construct($date, $options, $defaultOptions) {
      parent::__construct($date, $options, $defaultOptions);
    }

    public function __toString() {
      $this->
        table($this->getOption('tableAttrs'))->
          insertTableHead()->
          insertTableBody()->
        close('table');
      
      return parent::__toString();
    }
    
    abstract protected function &insertTableHead();
    abstract protected function &insertTableBody();

    protected function &insertDayNamesHeader() {
      $weekStartDay = $this->weekStart()->day();
      $currentDateTime = $this->weekStart();
      $dayNamesTemplate = '';
      for($col = 0; $col < 7; $col++) {
        $currentDateTime->day($weekStartDay + $col);
        $weekdayNumber = ($col + $this->getOption('weekStart')) % 7;
        $weekdayLabel = $this->dayNamesTemplate($weekdayNumber, clone $currentDateTime);
        self::tryFilter('dayNamesFilter', $weekdayLabel, clone $currentDateTime);
        $headerCellClass = $this->optionsClass(
          'headerCellClass',
          $this->getOption('weekdayClasses')[$weekdayNumber]
        );
        
        $this->
          th($headerCellClass)->
            append($weekdayLabel)->
          close('th');
      }
      
      return $this;
    }
  }

  /**
   * Creates month view for a given date.
   */
  class SimpleCalendarViewMonth extends SimpleCalendarTableBasedView {
    private static $defaultOptions = array(
      'eventTemplate' => '<b>%start[H:i]</b> %title',
      'dayNamesTemplate' => '%abbrev'
    );

    private $monthStart;  
    private $daysInMonth;
    
    public function __construct($date=null, $options=array()) {
      parent::__construct($date, $options, self::$defaultOptions);
      $this->monthStart = $this->date->copy()->day(1);
      $this->daysInMonth = $this->date->format('t') + 0;
      $this->nextDate = $this->date->copy()->modify('+1 month');
      $this->previousDate = $this->date->copy()->modify('-1 month');
    }

    public function nextDate() {
      return $this->nextDate;
    }

    public function previousDate() {
      return $this->previousDate;
    }
    
    protected function &insertTableHead() {
      return $this->
        thead()->
          tr($this->optionsClass('headerRowClass'))->
            insertDayNamesHeader()->
          close('tr')->
        close('thead');
    }

    protected function &insertTableBody() {
      $this->tbody();
      $dayOfMonth = 1;
      for($rowIndex = 0; $dayOfMonth <= $this->daysInMonth; $rowIndex++)
        $this->insertContentRow($dayOfMonth, $rowIndex);
      
      return $this->close('tbody');
    }
    
    private function &insertContentRow(&$dayOfMonth, $rowIndex) {
      $dayOfWeek = $dayOfMonth == 1 ?
        ($this->monthStart->format('w') - $this->getOption('weekStart') + 7) % 7 :
        0;
      
      $contentRowAttrs = $this->optionsClass(
        'contentRowClass',
        $this->getOption($rowIndex % 2 == 0 ? 'oddRowClass' : 'evenRowClass')
      );
      $this->tr($contentRowAttrs);
      for($col = 0; $col < 7; $col++) {
        $isNonMonthDay = $col < $dayOfWeek || $dayOfMonth > $this->daysInMonth;
        $currentDateTime = $col < $dayOfWeek ?
          $this->date->copy()->day($col - $dayOfWeek + 1) :
          $this->date->copy()->day($dayOfMonth);
        
        $this->
          td(array('class' => $this->getDayClass($col, $isNonMonthDay, $currentDateTime)))->
            insertDayContent($currentDateTime, $isNonMonthDay)->
          close('td');

        if($col >= $dayOfWeek)
          $dayOfMonth++;
      }
      
      return $this->close('tr');
    }
    
    private function getDayClass($col, $isNonMonthDay, $currentDateTime) {
      $classes = array();
      $classes[] = $this->getOption('bodyCellClass');
      $classes[] = $this->getOption($isNonMonthDay ? 'nonMonthDayClass' : 'monthDayClass');
      $classes[] = $this->getOption('weekdayClasses')[($col + $this->getOption('weekStart')) % 7];
      if($this->isToday($currentDateTime))
        $classes[] = $this->getOption('todayClass');
      
      return join($classes, ' ');
    }
    
    private function &insertDayContent($dateTime, $isNonMonthDay=false) {
      $dayNumber = $dateTime->day();
      self::tryFilter('dayNumbersFilter', $dayNumber, $dateTime->copy());
      $this->generateEvents($dateTime, $isNonMonthDay);
      
      return $this->
        div($this->optionsClass('dayNumberClass'))->
          append($dayNumber)->
        close('div')->
        div($this->optionsClass('eventFrameClass'))->
          insertEventList(true)->
          insertEventList(false)->
        close('div');
    }
  }

  /**
   * Creates basic elements of a day-based view for a given date.
   */
  abstract class SimpleCalendarViewDayBased extends SimpleCalendarTableBasedView {
    public function __construct($date, $options, $defaultOptions) {
      parent::__construct($date, $options, $defaultOptions);
    }

    /**
     * Creates a <div>-based column containing the day time (00:00-23:00).
     */
    protected function insertTimeColumn() {
      $this->div($this->optionsClass('timeContainerClass'));
      $currentDateTime = $this->date->copy();
      for($hour = 0; $hour < 24; $hour++) {
        $currentDateTime->hour($hour);
        $timeCellAttrs = $this->optionsClass(
          'timeCellClass',
          $this->getOption($hour % 2 == 1 ? 'evenRowClass' : 'oddRowClass')
        );
        if('' != $this->getOption('timeCellIDPrefix'))
          $timeCellAttrs['id'] = sprintf("%s%02d", $this->getOption('timeCellIDPrefix'), $hour);
        $this->
          div($timeCellAttrs)->
            append($this->getTime($currentDateTime))->
          close('div');
      }
      return $this->close('div');
    }
    
    private function getTime($currentDateTime) {
      $timeString = $currentDateTime->format($this->getOption('timeFormat'));
      self::tryFilter('timeFilter', $timeString, $currentDateTime->copy());
      
      return $timeString;
    }

    /**
     * Creates a <div>-based column containing the non-all-day event for a given date and fake rows
     * to create the illusion of a table.
     */
    protected function insertEventColumn($isAllDay, $date=null) {
      $this->generateEvents($date ? $date : $this->date);
      $this->
        div($this->optionsClass('eventFrameClass'))->
          insertEventList($isAllDay);
      
      if(! $isAllDay) {
        for($hour = 0; $hour < 24; $hour++) {
          $eventCellAttrs = $this->optionsClass(
            'eventCellClass',
            $this->getOption($hour % 2 == 1 ? 'evenRowClass' : 'oddRowClass')
          );
          $this->
            div($eventCellAttrs)->
              append($this->getOption('emptyValue'))->
            close('div');
        }
      }
      
      return $this->close('div');
    }
  }

  /**
   * Creates a week view for a given date.
   */
  class SimpleCalendarViewWeek extends SimpleCalendarViewDayBased {
    private static $defaultOptions = array(
      'eventTemplate' => '<b>%start[H:i]&ndash;%end[H:i]</b><br />%title',
      'dayNamesTemplate' => '%abbrev, %date[d.m.]'
    );
    
    /**
     * Constructs a view of the given day. $date is an array with the keys 'year' and/or 'month'. If
     * a key is ommitted it will be replaced with today's value. The output is widely customisable
     * through the $options array as follows.
     */
    public function __construct($date=null, $options=array()) {
      parent::__construct($date, $options, self::$defaultOptions);
      $this->nextDate = $this->date->copy()->modify('+1 week');
      $this->previousDate = $this->date->copy()->modify('-1 week');
    }

    public function nextDate() {
      return $this->nextDate;
    }

    public function previousDate() {
      return $this->previousDate;
    }
    
    protected function &insertTableHead() {
      return $this->
        thead()->
          tr($this->optionsClass('headerRowClass'))->
            th($this->optionsClass('timeColumnHeaderClass'))->
              append($this->getOption('emptyValue'))->
            close('th')->
            insertDayNamesHeader()->
          close('tr')->
          tr($this->optionsClass('allDayRowClass'))->
            th($this->optionsClass('allDayTimeColumnClass'))->
              append($this->getOption('emptyValue'))->
            close('th')->
            insertEventColumns(true, 'th')->
          close('tr')->
        close('thead');
    }
    
    protected function &insertTableBody() {
      return $this->
        tbody()->
          tr($this->optionsClass('contentRowClass'))->
            td($this->optionsClass('timeColumnClass'))->
              insertTimeColumn()->
            close('td')->
            insertEventColumns(false)->
          close('tr')->
        close('tbody');
    }
    
    protected function &insertEventColumns($isAllDay, $cellTag='td') {
      $weekStartDay = $this->weekStart()->day();
      $currentDateTime = $this->weekStart();
      for($col = 0; $col < 7; $col++) {
        $currentDateTime->day($weekStartDay + $col);
        
        $this->
          open($cellTag, array('class' => $this->getDayClass($col, $currentDateTime)))->
            insertEventColumn($isAllDay, $currentDateTime)->
          close($cellTag);
      }
      
      return $this;
    }
    
    protected function getDayClass($col, $currentDateTime) {
      $classes = array();
      $classes[] = $this->getOption('eventColumnClass');
      $classes[] = $this->getOption('weekdayClasses')[($col + $this->getOption('weekStart')) % 7];
      if($this->isToday($currentDateTime))
        $classes[] = $this->getOption('todayClass');
      
      return join($classes, ' ');
    }
  }

  class SimpleCalendarViewDay extends SimpleCalendarViewDayBased {
    private static $defaultOptions = array(
      'eventTemplate' => '<b>%start[H:i]&ndash;%end[H:i]</b><br />%title'
    );
    
    public function __construct($date=null, $options=array()) {
      parent::__construct($date, $options, self::$defaultOptions);
      $this->nextDate = $this->date->copy()->modify('+1 day');
      $this->previousDate = $this->date->copy()->modify('-1 day');
    }

    public function nextDate() {
      return $this->nextDate;
    }

    public function previousDate() {
      return $this->previousDate;
    }

    protected function &insertTableHead() {
      $dateString = $this->date->format($this->getOption('dateFormat'));
      self::tryFilter('headerFilter', $dateString, $this->date->copy());
      $headerCellAttrs = array(
        'class' => join(array(
          $this->getOption('headerCellClass'),
          $this->getOption('weekdayClasses')[($this->date->format('w') + $this->getOption('weekStart')) % 7],
          $this->isToday() ? 'today' : ''
        ), ' '),
        'colspan' => 2
      );
      
      return $this->
        thead()->
          tr($this->optionsClass('headerRowClass'))->
            th($headerCellAttrs)->
              append($dateString)->
            close('th')->
          close('tr')->
          tr($this->optionsClass('allDayRowClass'))->
            th($this->optionsClass('allDayTimeColumnClass'))->
              append($this->getOption('emptyValue'))->
            close('th')->
            th($this->optionsClass('allDayEventColumnClass'))->
              insertEventColumn(true)->
            close('th')->
          close('tr')->
        close('thead');
    }
    
    protected function &insertTableBody() {
      $weekday = ($this->date->format('w') + $this->getOption('weekStart')) % 7;
      return $this->
        tbody()->
          tr($this->optionsClass('contentRowClass'))->
            td($this->optionsClass('timeColumnClass'))->
              insertTimeColumn()->
            close('td')->
            td($this->optionsClass('eventColumnClass', $this->getOption('weekdayClasses')[$weekday]))->
              insertEventColumn(false)->
            close('td')->
          close('tr')->
        close('tbody');
    }
  }

  /**
   * Creates list view beginning with the given date.
   */
  class SimpleCalendarViewList extends SimpleCalendarView {
    private static $defaultOptions = array(
      'eventTemplate' => '<b>%start[H:i]&empsp;%end[H:i]</b> %title'
    );

    public function __construct($date=null, $options=array()) {
      parent::__construct($date, $options, self::$defaultOptions);
      $this->nextDate = null;
      $this->previousDate = null;
    }

    public function nextDate() {
      if(is_null($this->nextDate))
        $this->computeNextDate();
      return $this->nextDate;
    }

    public function previousDate() {
      if(is_null($this->previousDate))
        $this->computePreviousDate();
      return $this->previousDate;
    }
    
    protected function computeNextDate() {
      $numDays = 0;
      $numEvents = 0;
      $currentDateTime = $this->date->copy();
      while($numEvents < $this->getOption('maxEvents') &&
            $numDays < $this->getOption('maxDays')) {
        $this->generateEvents($currentDateTime);
        $numDays++;
        $numEvents += count($this->events);
        $currentDateTime = $currentDateTime->modify('+1 day');
      }
      $this->nextDate = $currentDateTime;
    }
    
    protected function computePreviousDate() {
      $numDays = 0;
      $numEvents = 0;
      $currentDateTime = $this->date->copy()->modify('-1 day');
      while($numEvents < $this->getOption('maxEvents') &&
            $numDays < $this->getOption('maxDays')) {
        $this->generateEvents($currentDateTime);
        $numDays++;
        $numEvents += count($this->events);
        $currentDateTime = $currentDateTime->modify('-1 day');
      }
      $this->previousDate = $currentDateTime->modify('+1 day');
    }

    public function __toString() {
      $this->
        ul($this->getOption('dayListAttrs'))->
          insertDayList()->
        close('ul');
      
      return parent::__toString();
    }
    
    protected function &insertDayList() {
      $numDays = 0;
      $numEvents = 0;
      $currentDateTime = $this->date->copy();
      while($numEvents < $this->getOption('maxEvents') &&
            $numDays < $this->getOption('maxDays')) {
        $this->generateEvents($currentDateTime);
        if(count($this->events) > 0)
          $this->
            li()->
              insertDayHeading($currentDateTime)->
              insertEventList(true)->
              insertEventList(false)->
            close('li');
        $numDays++;
        $numEvents += count($this->events);
        $currentDateTime = $currentDateTime->modify('+1 day');
      }
      $this->nextDate = $currentDateTime;

      return $this;
    }

    protected function &insertDayHeading($currentDateTime) {
      $dateString = $this->date->format($this->getOption('dateFormat'));
      self::tryFilter('headerFilter', $dateString, $currentDateTime->copy());
      $dayHeadingClass = $this->optionsClass(
        'dayHeadingClass',
        $this->getOption('weekdayClasses')[$currentDateTime->format('w')]
      );
      
      return $this->
        h3($dayHeadingClass)->
          append($dateString)->
        close('h3');      
    }
  }
}
?>
