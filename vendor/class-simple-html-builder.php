<?php
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

if(! function_exists('array_peek')) {
  /**
   * Retrieve the last entry of array or a default value. This works only
   * for numerically indexed arrays. Never issues any errors.
   */
  function array_peek(&$array, $default = null) {
    $key = count($array) - 1;
    return isset($array[$key]) ? $array[$key] : $default;
  }
}

if(! class_exists('SimpleHTMLBuilder')) {
  /**
   * Builds a HTML or XML document using a simple and clean PHP interface. With the explicit method
   * interface any XML document may be generated. Meanwhile, for HTML documents the easy-to-use __call
   * interface is provided.
   *
   * This is intended for algorithmic building of (partial) HTML documents, e.g. as in my
   * SimpleCalendarView. Hopefully, this approach keeps the code clean by avoiding lots of `echo`, 
   * `print`, `sprintf`, ... statements.
   *
   * __Examples:__
   *
   *     $builder = new SimpleHTMLBuilder();
   *     $builder->
   *       html()->
   *         head()->
   *           title()->
   *             append('Test SimpleHTMLBuilder')->
   *         close(2)->
   *         body()->
   *           h2(array('id' => 'heading', 'title' => 'Lorem Ipsum & Testing'))->
   *             append('Lorem Ipsum & Testing', true)->
   *           close();
   *         if($highlightText) {
   *           $builder->div('highlight');
   *           p()->
   *             append('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy ' .
   *               'eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.')->
   *             br()->
   *             append('At vero eos et accusam et justo duo dolores et ea rebum.')->
   *           closeUntil('body', false)->
   *           
   *       closeAll(); // this is optional, see $options in __construct
   *
   *     $xml = new SimpleHTMLBuilder();
   *     $xml->open('myRoot')->open('anotherTag')->append('Some Content');
   */
  class SimpleHTMLBuilder {
    protected static $builderTags = array('a', 'abbr', 'acronym', 'address', 'applet', 'area', 'article',
      'aside', 'audio', 'b', 'base', 'basefont', 'bdi', 'bdo', 'big', 'blockquote', 'body', 'br',
      'button', 'canvas', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'command',
      'datalist', 'dd', 'del', 'details', 'dfn', 'dialog', 'dir', 'div', 'dl', 'dt', 'em', 'embed',
      'fieldset', 'figcaption', 'figure', 'font', 'footer', 'form', 'frame', 'frameset', 'head',
      'header', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'html', 'i', 'iframe', 'img', 'input',
      'ins', 'kbd', 'keygen', 'label', 'legend', 'li', 'link', 'main', 'map', 'mark', 'menu', 'meta',
      'meter', 'nav', 'noframes', 'noscript', 'object', 'ol', 'optgroup', 'option', 'output', 'p',
      'param', 'pre', 'progress', 'q', 'rp', 'rt', 'ruby', 's', 'samp', 'script', 'section', 'select',
      'small', 'source', 'span', 'strike', 'strong', 'style', 'sub', 'summary', 'sup', 'table',
      'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'time', 'title', 'tr', 'track', 'tt', 'u',
      'ul', 'var', 'video', 'wbr');
    protected static $emptyHTMLTags = array('area', 'base', 'basefont', 'br', 'col', 'embed', 'frame',
      'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'wbr');
      
    private static $defaultOptions = array(
      'xmlStyle' => true,
      'openTags' => 'warning',
      'nonMatchingClose' => 'error',
      'debugTrace' => false
    );
    
    protected $content;
    private $options;
    protected $elementStack;
    
    /**
     * Constructs a new builder class with $options:
     *
     * - __xmlStyle:__ use self-closing tags (e.g. &lt;br /&gt;) if true (default: true)
     * - __openTags:__ defines handling of open tags on output request (default: 'warning'); allowed
     *   values:
     *     - __error:__ trigger an error
     *     - __warning:__ trigger a warning
     *     - __notice:__ trigger a notice
     *     - __autoClose:__ automatically close all tags
     *     - any other value is treated as a callback
     *       `function ($currentContent, $elementStack, $backtrace);`
     * - __nonMatchingClose:__ defines handling of close requests with non-matching tag name
     *   (default: 'error'); allowed values:
     *     - __error:__ trigger an error
     *     - __warning:__ trigger a warning
     *     - __notice:__ trigger a notice
     *     - any other value is treated as a callback
     *       `function ($currentContent, $expectedName, $foundName, $backtrace);`
     * - __debugTrace:__ saves the sources of open() calls if enabled (default: false)
     */
    public function __construct($options=array()) {
      $this->content = '';
      $this->options = array_merge(self::$defaultOptions, $options);
      $this->elementStack = array();
    }

    protected function getOption($name, $default=null) {
      return isset($this->options[$name]) ? $this->options[$name] : $default;
    }
        
    /**
     * Directly append any string to the document. If $escapeHTML is true then the $content is
     * filtered by PHP's htmlentities prior to appending.
     *
     * @returns reference to $this
     */
    public function &append($content, $escapeHTML=false) {
      $this->content .= $escapeHTML ? htmlentities($content) : $content;

      return $this;
    }
    
    /**
     * Append an opening tag $name with $attrSpecs.
     *
     * @see makeAttributes($attrSpecs)
     * @returns reference to $this
     */
    public function &open($name, $attrSpecs='') {
      array_push(
        $this->elementStack,
        array(
          'name' => $name,
          'backtrace' => $this->getOption('debugTrace') ? debug_backtrace() : null
        )
      );
      return $this->append('<' . $name . self::makeAttributes($attrSpecs) . '>');
    }
    
    /**
     * Append an empty tag $name with $attrSpecs.
     *
     * @see makeAttributes($attrSpecs)
     * @see the options 'xmlStyle'
     * @returns reference to $this
     */
    public function &emptyTag($name, $attrSpecs='') {
      $xmlClose = $this->getOption('xmlStyle') ? ' /' : '';
      $attributes = self::makeAttributes($attrSpecs);
      return $this->append("<$name$attributes$xmlClose>");
    }
    
    /**
     * $attrSpecs may be one of:
     * - __associative array:__ The key-value pairs will be inserted into the tag. The values will
     *   be filtered automatically by PHP's htmlentities.
     * - __CSS selector variant:__ A CSS-selector-style string to define the id or classes of the
     *   tag, e.g. '#id.class1.class2'.
     * - __HTML classes string:__ Any other string will be interpreted as a string of HTML classes.
     * - __other values:__ Other values will raise a warning.
     *
     * @returns Attribute string
     */
    protected static function makeAttributes($attrSpecs) {
      if(empty($attrSpecs)) {
        return '';
      } elseif(is_array($attrSpecs)) {
        $attributeString = '';
        foreach($attrSpecs as $property => $value)
          if(isset($value))
            $attributeString .= sprintf(' %s="%s"', $property, htmlentities($value));
        return $attributeString;
      } elseif(is_string($attrSpecs)) {
        // The regexp is generated from:
        //   $h = "[0-9a-f]";
        //   $unicode = "\\\\$h{1,6}(\\r\\n|[ \\t\\r\\n\\f])?";
        //   $escape = "($unicode|\\\\[^\\r\\n\\f0-9a-f])";
        //   $nonascii = "[\\x200-\\x377]";
        //   $nmchar = "([_a-z0-9-]|$nonascii|$escape)";
        //   $nmstart = "([_a-z]|$nonascii|$escape)";
        //   $ident = "-?$nmstart$nmchar*";
        //   $selector = "/^(?P<id>#$ident)?(?P<classes>(\\.$ident)*)$/";
        // Thanks to BalusC (http://stackoverflow.com/questions/2812072/allowed-characters-for-css-identifiers)
        static $cssSelectorRegexp = '/^(?P<id>#-?([_a-z]|[\\x200-\\x377]|(\\\\[0-9a-f]{1,6}(\\r\\n|[ \\t\\r\\n\\f])?|\\\\[^\\r\\n\\f0-9a-f]))([_a-z0-9-]|[\\x200-\\x377]|(\\\\[0-9a-f]{1,6}(\\r\\n|[ \\t\\r\\n\\f])?|\\\\[^\\r\\n\\f0-9a-f]))*)?(?P<classes>(\\.-?([_a-z]|[\\x200-\\x377]|(\\\\[0-9a-f]{1,6}(\\r\\n|[ \\t\\r\\n\\f])?|\\\\[^\\r\\n\\f0-9a-f]))([_a-z0-9-]|[\\x200-\\x377]|(\\\\[0-9a-f]{1,6}(\\r\\n|[ \\t\\r\\n\\f])?|\\\\[^\\r\\n\\f0-9a-f]))*)*)$/';
        $matches = array();
        if(preg_match($cssSelectorRegexp, $attrSpecs, $matches)) {
          $id = empty($matches['id']) ?
            '' :
            'id="'.substr($matches['id'], 1).'"';
          $classes = empty($matches['classes']) ?
            '' :
            'class="'.str_replace('.', ' ', substr($matches['classes'], 1)).'"';

          return ' '.trim("$id $classes");
        }
      }

      trigger_error('argument $attrSpecs has an unknown format.', E_USER_WARNING);
      return '';
    }
    
    /**
     * Insert closing tags
     *   - for the $count last open tags. Handles too large values for $count gracefully.
     *   - for tags with specified names. Takes an arbitrary number of arguments as names.
     *
     * __Examples__:
     *
     *     $builder = new SimpleHTMLBuilder();
     *     $builder->body()->div()->close(5); // <body><div></div></body>
     *
     *     $builder = new SimpleHTMLBuilder();
     *     $builder->body()->div()->close()->div()->close(2); // <body><div></div><div></div></body>
     *
     *     $builder = new SimpleHTMLBuilder();
     *     $builder->body()->div()->close('div', 'body'); // <body><div></div></body>
     *
     *     $builder = new SimpleHTMLBuilder();
     *     $builder->body()->div()->close('p', 'body'); // ERROR
     *
     * @returns reference to $this
     */
    public function &close($count=1) {
      if(is_int($count))
        for($i = 0; $i < $count; $i++)
          $this->closeSingle();
      else
        foreach(func_get_args() as $tagName)
          $this->closeSingle($tagName);
      
      return $this;
    }
    
    /**
     * Insert closing tags until a tag with the specified name is encountered.
     * If the given tag is not found at all then all tags will be closed.
     *
     * The follwing $options are available:
     * - __excludeTag:__ do not close the given tag itself (default: false)
     * - __maxCount:__ close at most this number of tags (default: PHP_INT_MAX)
     *
     * __Examples__:
     *
     *     $builder = new SimpleHTMLBuilder();
     *     $builder->body()->div()->closeUntil('body');
     *     // <body><div></div></body>
     *
     *     $builder = new SimpleHTMLBuilder();
     *     $builder->body()->div()->closeUntil('body', array('excludeTag' => true));
     *     // <body><div></div>
     *
     *     $builder = new SimpleHTMLBuilder();
     *     $builder->body()->div()->div()->closeUntil('body', array('maxCount' => 2));
     *     // <body><div><div></div></div>
     *
     *     $builder = new SimpleHTMLBuilder();
     *     $builder->body()->div()->closeUntil('html');
     *     // <body><div></div></body>
     *
     * @returns reference to $this
     */
    public function &closeUntil($tagName, $options=array()) {
      static $defaultOptions = array(
        'excludeTag' => false,
        'maxCount' => PHP_INT_MAX
      );
      $options = array_merge($defaultOptions, $options);

      $numClosed = 0;
      $maxCount = $options['maxCount'];
      while(count($this->elementStack) > 0 &&
            $numClosed < $maxCount &&
            array_peek($this->elementStack)['name'] != $tagName) {
        $this->closeSingle();
        $numClosed++;
      }

      if(! $options['excludeTag'] && 
         $numClosed < $maxCount &&
         array_peek($this->elementStack)['name'] == $tagName) {
        $this->closeSingle();
      }
      
      return $this;
    }
    
    /**
     * Insert closing tags for the all open tags.
     *
     * @returns reference to $this
     */
    public function &closeAll() {
      while($this->closeSingle());
      
      return $this;
    }

    protected function closeSingle($expectedName=null) {
      if(count($this->elementStack) > 0) {
        $name = array_pop($this->elementStack)['name'];
        if(! isset($expectedName) || $name == $expectedName)
          $this->append('</' . $name . '>');
        else
          self::handleNonMatchingClose($expectedName, $name);
      }
        
      return count($this->elementStack);
    }
    
    /**
     * Return an array of names of open tags.
     *
     * @returns array of names of open tags
     */
    public function openTags() {
      return array_map(function($e) { return $e['name']; }, $this->elementStack);
    }
    
    /**
     * Clear all generated content.
     *
     * @returns reference to $this
     */
    public function &clear() {
      $this->content = '';
      return $this;
    }
    
    /**
     * Insert tag $name with optional $attrSpecs as first argument. This is only defined for valid
     * (standard HTML tags)[http://www.w3schools.com/tags/]. It automagically takes empty tags into
     * account.
     *
     * @see makeAttributes($attrSpecs)
     * @returns reference to $this
     */
    public function &__call($name, $callArgs) {
      if(! in_array($name, self::$builderTags))
        self::unkownFunctionCall($name, $callArgs);
      
      $attributes = isset($callArgs[0]) ? $callArgs[0] : '';
      
      if(in_array($name, self::$emptyHTMLTags))
        return $this->emptyTag($name, $attributes);
      else
        return $this->open($name, $attributes);
    }
    
    protected static function unkownFunctionCall($name, $callArgs) {
      trigger_error("Call to undefined method '$name'", E_USER_ERROR);
    }
    
    /**
     * Return the current document potentially closing open tags first (see options).
     *
     * @returns current document
     */
    public function __toString() {
      if(count($this->elementStack) > 0 && ! empty($this->getOption('openTags')))
        $this->handleOpenTags();
      
      return $this->content;
    }
    
    protected function handleOpenTags() {
      switch($this->getOption('openTags')) {
        case 'error':
          $this->triggerOpenTagsError(E_USER_ERROR);
          break;
        case 'warning':
          $this->triggerOpenTagsError(E_USER_WARNING);
          break;
        case 'notice':
          $this->triggerOpenTagsError(E_USER_NOTICE);
          break;
        case 'autoClose':
          $this->closeAll();
          break;
        default:
          call_user_func($this->getOption('openTags'), $this->content, $this->elementStack);
          break;
      }
    }
    
    protected function triggerOpenTagsError($level) {
      $openTags = join(
        array_map(function ($tag) { return $tag['name']; }, $this->elementStack),
        ', '
      );
      
      trigger_error(sprintf('%d tags left open: %s', count($this->elementStack), $openTags), $level);
    }
    
    protected function handleNonMatchingClose($expectedName, $foundName) {
      switch($this->getOption('nonMatchingClose')) {
        case 'error':
          $this->triggerNonMatchingCloseError($expectedName, $foundName, E_USER_ERROR);
          break;
        case 'warning':
          $this->triggerNonMatchingCloseError($expectedName, $foundName, E_USER_WARNING);
          break;
        case 'notice':
          $this->triggerNonMatchingCloseError($expectedName, $foundName, E_USER_NOTICE);
          break;
        default:
          call_user_func(
            $this->getOption('nonMatchingClose'),
            $this->content,
            $expectedName,
            $foundName
          );
          break;
      }
    }
    
    protected function triggerNonMatchingCloseError($expectedName, $foundName, $level) {
      trigger_error(
        "Tried to close <b>&lt;$expectedName&gt;</b> but found <b>&lt;$foundName&gt;</b>",
        $level
      );
    }
    
    /**
     * Evaluates the current content as a template with the given $arrayOrObject. The field values
     * get escaped if $escapeHTML evaluates to true.
     *
     * @returns result string
     * @see evalTemplateString()
     */
    public function evalTemplate($arrayOrObject, $escapeHTML=false) {
      return self::evalTemplateString((string) $this, $arrayOrObject, $escapeHTML);
    }
    
    /**
     * Inserts the object details into a template. Template fields are inserted as strings or as
     * formatted DateTime. The field values get escaped if $escapeHTML evaluates to true.
     *
     * __Field Syntax:__
     * 
     *     field          ::= variable | literalPercent
     *     literalPercent ::= "%%"
     *     variable       ::= "%" fieldName ["[" dateFormat "]" | "$" numberFormat]
     *     fieldName      ::= letters | "_" | [fieldNameExt]
     *     fieldNameExt   ::= (alphaNum | "_" | letters) [fieldNameExt]
     *
     *   Notes:
     *   * dateFormat is a string accepted by date
     *   * numberFormat is a conversion specification accepted by sprintf() without the leading '%'.
     *     Of course, the '%%' conversion specification will not be accepted
     *
     * __Example Templates:__
     *
     *   * '<b>%start[H:i]</b> %title'
     *   * '<b>%start[d.M.Y], %start[H:i] &ndash; %end[H:i]</b> &emsp; %title'
     *
     * __Notes:__
     *
     *   * Every field of the object will be available to the template.
     *   * To include a literal `%` you have to double it `%%` unless it is inside a dateFormat.
     *   * Identifiers are case-sensitive.
     *   * dateFormat must not contain `]`. If you need to use a `]` in a format, you have to split
     *     the field, e.g. the format `[H:i] d.m.Y` for the field `start` becomes
     *
     *         [%start[H:i]] %start[d.m.Y]
     */
    public static function evalTemplateString($template, $arrayOrObject, $escapeHTML=false) {
      $object = is_object($arrayOrObject) ? $arrayOrObject : (object) $arrayOrObject;
      
      static $fieldPattern = '/%(?<fieldName>%|[a-zA-Z_][a-zA-Z0-9_]*)(?:\[(?<dateFormat>[^\]]+)\]|\$(?<numberFormat>[+-]?(?:[0 ]|\'.)?[-]?[0-9]*(?:\.[0-9]+)?[bcdeEfFgGosuxX]))?/';
      return preg_replace_callback($fieldPattern, function ($match) use (&$object, $escapeHTML) {
        $str = '';
        if($match['fieldName'] == '%')
          $str = '%';
        elseif(empty($match['dateFormat']) && empty($match['numberFormat']))
          $str = $object->$match['fieldName'];
        else if(! empty($match['dateFormat']))
          $str = $object->$match['fieldName']->format($match['dateFormat']);
        else
          $str = sprintf('%'.$match['numberFormat'], $object->$match['fieldName']);

        return $escapeHTML ? htmlentities($str) : $str;
      }, $template);
    }
  }


  if(php_sapi_name() == 'cli') {
    $unitTests = array();
    
    $unitTests['Extensive'] = function() {
      $expected = '<html><head><title>Test SimpleHTMLBuilder</title></head><body><h2 id="heading" title="Lorem Ipsum &amp; Testing">Lorem Ipsum &amp; Testing</h2><p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.<br />At vero eos et accusam et justo duo dolores et ea rebum.</p></body></html>';
      $builder = new SimpleHTMLBuilder();
      $builder->
        html()->
          head()->
            title()->
              append('Test SimpleHTMLBuilder')->
          close(2)->
          body()->
            h2(array('id' => 'heading', 'title' => 'Lorem Ipsum & Testing'))->
              append('Lorem Ipsum & Testing', true)->
            close()->
            p()->
              append('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy ' .
                'eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.')->
              br()->
              append('At vero eos et accusam et justo duo dolores et ea rebum.')->
        closeAll();
      
      return $builder == $expected;
    };
    
    $unitTests['Make Attributes'] = function() {
      $expected = array(
        '<br id="foo" class="bar baz" />',
        '<br id="foo" class="bar baz" />',
        '<br id="foo" />',
        '<br class="bar" />',
        '<br title="foo&amp;bar" />',
      );
      $builder = new SimpleHTMLBuilder();
      $res[] = (string) $builder->clear()->br(array('id' => 'foo', 'class' => 'bar baz'));
      $res[] = (string) $builder->clear()->br('#foo.bar.baz');
      $res[] = (string) $builder->clear()->br('#foo');
      $res[] = (string) $builder->clear()->br('.bar');
      $res[] = (string) $builder->clear()->br(array('title' => 'foo&bar'));
      
      return $res == $expected;
    };
    
    $unitTests['Clearing'] = function() {
      $builder = new SimpleHTMLBuilder();
      $builder->html()->close();
      $builder->clear();
      
      return $builder == '';
    };
    
    $unitTests['XML Style'] = function() {
      $expected = '<html><br></html>';
      $builder = new SimpleHTMLBuilder(array('xmlStyle' => false));
      $builder->html()->br()->closeAll();
      
      return $builder == $expected;
    };
    
    $unitTests['Error on Open Tags'] = function() {
      $result = false;
      $oldErrorHandler = set_error_handler(function ($errorNo, $errorString) use (&$result) {
        $result = $errorNo == E_USER_ERROR;
      });
      
      (string) (new SimpleHTMLBuilder(array('openTags' => 'error')))->html();
      
      set_error_handler($oldErrorHandler);
      
      return $result;
    };
    
    $unitTests['Warning on Open Tags'] = function() {
      $result = false;
      $oldErrorHandler = set_error_handler(function ($errorNo, $errorString) use (&$result) {
        $result = $errorNo == E_USER_WARNING;
      });
      
      (string) (new SimpleHTMLBuilder(array('openTags' => 'warning')))->html();
      
      set_error_handler($oldErrorHandler);
      
      return $result;
    };
    
    $unitTests['Notice on Open Tags'] = function() {
      $result = false;
      $oldErrorHandler = set_error_handler(function ($errorNo, $errorString) use (&$result) {
        $result = $errorNo == E_USER_NOTICE;
      });
      
      (string) (new SimpleHTMLBuilder(array('openTags' => 'notice')))->html();
      
      set_error_handler($oldErrorHandler);
      
      return $result;
    };
    
    $unitTests['Callback on Open Tags'] = function() {
      $result = false;
      (string) (new SimpleHTMLBuilder(array('openTags' => function () use (&$result) {
        $result = true;
      })))->html();
        
      return $result;
    };
      
    $unitTests['Auto Close on Open Tags'] = function() {
      $expected = '<html><p></p></html>';
      $builder = new SimpleHTMLBuilder(array('openTags' => 'autoClose'));
      $builder->html()->p();
      
      return $builder == $expected;
    };
    
    $unitTests['Error on Non-Matching Close'] = function() {
      $result = false;
      $oldErrorHandler = set_error_handler(function ($errorNo, $errorString) use (&$result) {
        $result = $errorNo == E_USER_ERROR;
      });
      
      (string) (new SimpleHTMLBuilder(array('nonMatchingClose' => 'error')))->html()->close('other');
      
      set_error_handler($oldErrorHandler);
      
      return $result;
    };
    
    $unitTests['Warning on Non-Matching Close'] = function() {
      $result = false;
      $oldErrorHandler = set_error_handler(function ($errorNo, $errorString) use (&$result) {
        $result = $errorNo == E_USER_WARNING;
      });
      
      (string) (new SimpleHTMLBuilder(array('nonMatchingClose' => 'warning')))->html()->close('other');
      
      set_error_handler($oldErrorHandler);
      
      return $result;
    };
    
    $unitTests['Notice on Non-Matching Close'] = function() {
      $result = false;
      $oldErrorHandler = set_error_handler(function ($errorNo, $errorString) use (&$result) {
        $result = $errorNo == E_USER_NOTICE;
      });
      
      (string) (new SimpleHTMLBuilder(array('nonMatchingClose' => 'notice')))->html()->close('other');
      
      set_error_handler($oldErrorHandler);
      
      return $result;
    };
    
    $unitTests['Callback on Non-Matching Close'] = function() {
      $result = false;
      (string) (new SimpleHTMLBuilder(array('nonMatchingClose' => function () use (&$result) {
        $result = true;
      })))->html()->close('other');
        
      return $result;
    };
    
    $unitTests['Eval Template'] = function() {
      date_default_timezone_set('UTC');
      
      $expected = 'My String at 08:39 06.02.1970 with 3.14 % of pie';
      $template = '%string at %date[H:i d.m.Y] with %number$3.2f % of pie';
      $object = (object) array(
        'string' => 'My String',
        'date' => new DateTime('@3141592'),
        'number' => 3.1415
      );
      $evaluated_static = SimpleHTMLBuilder::evalTemplateString($template, $object);
      $evaluated_object = (new SimpleHTMLBuilder())->append($template)->evalTemplate($object);

      return $evaluated_static == $expected &&
             $evaluated_object == $expected;
    };
    
    $unitTests['Eval Template with Escape HTML'] = function() {
      date_default_timezone_set('UTC');
      
      $expected = 'My &lt;String&gt; at 08:39 06.02.1970 with 3.14 % of pie';
      $template = '%string at %date[H:i d.m.Y] with %number$3.2f % of pie';
      $object = (object) array(
        'string' => 'My <String>',
        'date' => new DateTime('@3141592'),
        'number' => 3.1415
      );
      $evaluated_static = SimpleHTMLBuilder::evalTemplateString($template, $object, true);
      $evaluated_object = (new SimpleHTMLBuilder())->append($template)->evalTemplate($object, true);

      return $evaluated_static == $expected &&
             $evaluated_object == $expected;
    };

    $unitTests['Open Tags'] = function() {
      $builder = new SimpleHTMLBuilder();
      $builder->body()->div()->close('div')->p()->em();

      return array('body', 'p', 'em') == $builder->openTags();
    };

    $unitTests['Close Until (default options)'] = function() {
      $builder = new SimpleHTMLBuilder();
      $builder->body()->div()->closeUntil('body');

      return '<body><div></div></body>' == $builder;
    };
 
    $unitTests['Close Until (exclude tag)'] = function() {
      $builder = new SimpleHTMLBuilder(array('openTags' => function () {}));
      $builder->body()->div()->closeUntil('body', array('excludeTag' => true));

      return '<body><div></div>' == $builder;
    };
 
    $unitTests['Close Until (max count: 2)'] = function() {
      $builder = new SimpleHTMLBuilder(array('openTags' => function () {}));
      $builder->body()->div()->div()->closeUntil('body', array('maxCount' => 2));

      return '<body><div><div></div></div>' == $builder;
    };
 
    $unitTests['Close Until (non-existent tag)'] = function() {
      $builder = new SimpleHTMLBuilder();
      $builder->body()->div()->closeUntil('html');

      return '<body><div></div></body>' == $builder;
    };


    
    $passedCount = 0;
    foreach($unitTests as $name => $test) {
      $testResult = call_user_func($test);
      echo $name . ' Test ... ' . ($testResult ? 'passed' : 'failed') . PHP_EOL;
      if($testResult)
         $passedCount++;
    }
    
    echo '---' . PHP_EOL;
    echo 'Passed ' . $passedCount . ' of ' . count($unitTests) . ' tests.' . PHP_EOL;

    exit(count($unitTests) - $passedCount);
  }
}
?>
