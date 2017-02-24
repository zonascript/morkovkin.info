;(function(window, document, undefined)
{
    // Define FireCode namespace
    var FireCode = window.FireCode = {};

    // Predefined point constants
    FireCode.BOF = 'BOF';
    FireCode.EOF = 'EOF';
    FireCode.BOL = 'BOL';
    FireCode.EOL = 'EOL';

    // Predefined shift constants
    FireCode.PAGE = 'PAGE';
    FireCode.LINE = 'LINE';
    FireCode.WORD = 'WORD';
    FireCode.CHAR = 'CHAR';

    // Attaches FireCode instance to an element
    FireCode.attach = function(id)
    {
        var fireCode = new FireCode.Editor(),
            textAreaNode = document.getElementById(id),
            fireCodeNode = fireCode.attachEditor(textAreaNode);

        // Replace nodes
        fireCodeNode.style.position = 'absolute';
        textAreaNode.style.visibility = 'hidden';

        // Save fireCodeNode for detach
        textAreaNode['data-firecode'] = fireCodeNode;

        // Return fireCode instance
        return fireCode;
    };

    // Detaches FireCode instance from an element
    FireCode.detach = function(id)
    {
        var textAreaNode, fireCodeNode;

        if ((textAreaNode = document.getElementById(id)) &&
            (fireCodeNode = textAreaNode['data-firecode']))
        {
            textAreaNode.style.visibility = null; // Restore
            fireCodeNode.parentNode.removeChild(fireCodeNode);
        }
    };

    // Base object
    FireCode.Class = function()
    {};

    // Class inheritance
    FireCode.Class.extend = function(props)
    {
        var newClass = function()
        {
            this.init && this.init.apply(this, arguments);
        };

        if (Object.create)
        {
            newClass.prototype = Object.create(this.prototype);
        }
        else
        {
            var F = function() {};
            F.prototype = this.prototype;
            newClass.prototype = new F;
        }

        newClass.extend = FireCode.Class.extend;

        for (var name in props)
            if (typeof props[name] == "function")
                newClass.prototype[name] = props[name];

        return newClass;
    };



    // Scrolling frame
    FireCode.ScrollingFrame = FireCode.Class.extend
    ({
        init: function(topLine, topPosition, endLine, endPosition)
        {
            this.topLine = topLine;
            this.topPosition = topPosition;
            this.endLine = endLine;
            this.endPosition = endPosition;
        },

        collidesFrame: function(frame)
        {
            if (frame.topLine > this.endLine || frame.endLine < this.topLine) return 0;
            if (frame.topLine > this.topLine || frame.topLine < this.topLine) return this.topLine - frame.topLine;
        },

        collidesRange: function(range)
        {
            return (range.startLine !== range.endLine &&
                   (range.startLine >= this.topLine && range.startLine <= this.endLine ||
                    range.endLine >= this.topLine && range.endLine <= this.endLine ||
                    range.startLine <= this.topLine && range.endLine >= this.endLine ||
                    range.endLine <= this.topLine && range.startLine >= this.endLine));
        },

        containsPoint: function(point)
        {
            return this.topLine <= point.line &&
                   this.endLine >= point.line &&
                   (this.topPosition === null || this.topPosition <= point.position) &&
                   (this.endPosition === null || this.endPosition >= point.position);
        }

    });

    // Selection range
    FireCode.SelectionRange = FireCode.Class.extend
    ({
        init: function(startLine, startPosition, endLine, endPosition)
        {
            this.id = Date.now();
            this.startLine = startLine || 0;
            this.startPosition = startPosition || 0;
            this.fixedPosition = startPosition || 0;
            this.endLine = endLine || 0;
            this.endPosition = endPosition || 0;
        },

        getStartPoint: function()
        {
            return new FireCode.SelectionPoint(this.startLine, this.startPosition);
        },

        getEndPoint: function()
        {
            return new FireCode.SelectionPoint(this.endLine, this.endPosition);
        },

        getLeftPoint: function()
        {
            // TODO: Use point comparison functions
            if (this.startLine < this.endLine || this.startLine === this.endLine && this.startPosition < this.endPosition)
                return new FireCode.SelectionPoint(this.startLine, this.startPosition);
            else
                return new FireCode.SelectionPoint(this.endLine, this.endPosition);
        },

        getRightPoint: function()
        {
            // TODO: Use point comparison functions
            if (this.startLine > this.endLine || this.startLine === this.endLine && this.startPosition > this.endPosition)
                return new FireCode.SelectionPoint(this.startLine, this.startPosition);
            else
                return new FireCode.SelectionPoint(this.endLine, this.endPosition);
        },

        getLinesCount: function()
        {
            return Math.abs(this.startLine - this.endLine) + 1;
        },

        containsPoint: function(point, exclusive)
        {
            var leftPoint = this.getLeftPoint(),
                rightPoint = this.getRightPoint();

            // Single-line range
            if (leftPoint.line === point.line && rightPoint.line === point.line)
                return exclusive ?
                    leftPoint.position < point.position && rightPoint.position > point.position :
                    leftPoint.position <= point.position && rightPoint.position >= point.position;

            // Multiline range - top line
            else if (leftPoint.line === point.line)
                return exclusive ? leftPoint.position < point.position : leftPoint.position <= point.position;

            // Multiline range - end line
            else if (rightPoint.line === point.line)
                return exclusive ? rightPoint.position > point.position : rightPoint.position >= point.position;

            // Miltiline range - midle block
            else if (leftPoint.line < point.line && rightPoint.line > point.line)
                return true;
        },

        isEmpty: function()
        {
            return this.startLine === 0 &&
                   this.startPosition === 0 &&
                   this.endLine === 0 &&
                   this.endPosition === 0;
        },

        isCollapsed: function()
        {
            return this.startLine === this.endLine &&
                   this.startPosition === this.endPosition;
        },

        isMultiline: function()
        {
            return this.startLine != this.endLine;
        },

        equalsTo: function(range)
        {
            return this.startLine === range.startLine &&
                   this.startPosition === range.startPosition &&
                   this.endLine === range.endLine &&
                   this.endPosition === range.endPosition;
        },

        collapse: function()
        {
            this.endLine = this.startLine;
            this.endPosition = this.startPosition;
        },

        collapseTo: function(point)
        {
            this.startLine = point.line;
            this.startPosition = point.position;
            this.fixedPosition = point.position;
            this.endLine = point.line;
            this.endPosition = point.position;
        },

        extendTo: function(point)
        {
            this.startLine = point.line;
            this.startPosition = point.position;
            this.fixedPosition = point.position;
        },

        expandBy: function(point)
        {
            var leftPoint = this.getLeftPoint(),
                rightPoint = this.getRightPoint();

            this.startLine = point.line;
            this.startPosition = point.position;
            this.fixedPosition = point.position;

            if (point.line < leftPoint.line || point.line === leftPoint.line && point.position <= leftPoint.position)
            {
                this.endLine = rightPoint.line;
                this.endPosition = rightPoint.position;
            }

            if (point.line > rightPoint.line || point.line === rightPoint.line && point.position >= rightPoint.position)
            {
                this.endLine = leftPoint.line;
                this.endPosition = leftPoint.position;
            }
        },

        revert: function()
        {
            var startLine = this.startLine,
                startPosition = this.startPosition;

            this.startLine = this.endLine;
            this.startPosition = this.endPosition;
            this.fixedPosition = this.endPosition;
            this.endLine = startLine;
            this.endPosition = endPosition;
        },

        clone: function()
        {
            var range = new FireCode.SelectionRange();

            range.startLine = this.startLine;
            range.startPosition = this.startPosition;
            range.fixedPosition = this.fixedPosition;
            range.endLine = this.endLine;
            range.endPosition = this.endPosition;

            return range;
        }
    });

    // Selection point
    FireCode.SelectionPoint = FireCode.Class.extend
    ({
        init: function(line, position)
        {
            this.line = line || 0;
            this.position = position || 0;
        },

        compareTo: function(point)
        {
            if (this.line === point.line && this.position === point.position) return 0;
            else if (this.line < point.line || this.line === point.line && this.position < point.position) return -1;
            else if (this.line > point.line || this.line === point.line && this.position > point.position) return +1;
        }
    });

    // Editor component
    FireCode.Editor = FireCode.Class.extend
    ({
        init: function()
        {
            this._lines = [];
            this._lineWidth = 0;
            this._doubleClickTime = 300;
            this._wordSeparators = " ./\\()\"'-:,.;<>~!@#$%^&*|+=[]{}`~?";
            this._tabSize = 4;
            this._selection = [];
            this._smartHome = true;
            this._smartEnter = true;
            this._enumLines = 'auto'; // true, false, 'auto'
            this._animation = true;
        },

        getFacade: function() {return this._facade;},
        // getParser: function() {return this._parser;},
        // getStatus: function() {return this._status;},

        attachEditor: function(target)
        {
            // Editor text
            if (target.tagName === 'TEXTAREA')
                this.setText(target.value);
            else
                this.setText(target.innerHTML);

            // Create editor
            this.createEditor(target);

            // Detect sizing
            this.detectSizing();

            // Create selection
            this.addSelectionRange(0, 0, 0, 0);

            // Update layout
            this.updateLayout();

            // Update editor
            this.updateEditor(true);

            // Start cursor blinking
            this.pingCursor();

            // Attach event handlers
            this.attachHandlers();

            // Return root node
            return this._facade;
        },

        createEditor: function(target)
        {
            // Geterate HTML
            var html = '';

            html += '<div class="offset" tabindex="-1">';
            html += '<div class="layer backer"><pre></pre></div>';
            html += '<div class="layer marker"><pre></pre></div>';
            html += '<div class="layer editor"><pre></pre></div>';
            html += '<div class="layer cursor"><pre></pre></div>';
            html += '<div class="layer gutter"><pre></pre></div>';
            html += '</div>';
            html += '<div class="scroll-v"><div></div></div>';
            html += '<div class="scroll-h"><div></div></div>';
            html += '<div class="scroll-x"><div></div></div>';

            // Create facade node
            this._facade = document.createElement('DIV');
            this._facade.className = 'firecode';
            this._facade.tabIndex = '0';
            this._facade.innerHTML = html;

            // Find editor nodes
            this._offset = this._facade.childNodes[0];
            this._backer = this._offset.childNodes[0].firstChild;
            this._marker = this._offset.childNodes[1].firstChild;
            this._editor = this._offset.childNodes[2].firstChild;
            this._cursor = this._offset.childNodes[3].firstChild;
            this._gutter = this._offset.childNodes[4].firstChild;

            // Internal reference to window
            this._window = window;

            // Find scrollbar nodes
            this._scrollV = this._facade.childNodes[1];
            this._scrollH = this._facade.childNodes[2];
            this._expandV = this._facade.childNodes[1].firstChild;
            this._expandH = this._facade.childNodes[2].firstChild;

            // Hides square between scrollbars
            this._scrollX = this._facade.childNodes[3];

            // Copy dimensions
            this._facade.style.top = target.offsetTop + 'px';
            this._facade.style.left = target.offsetLeft + 'px';
            this._facade.style.height = target.offsetHeight + 'px';
            this._facade.style.width = target.offsetWidth + 'px';

            // Insert editor node into the DOM
            target.parentNode.insertBefore(this._facade, target);
        },

        detectSizing: function()
        {
            var test = document.createElement('DIV'),
                line = document.createElement('DIV'),
                bars = document.createElement('DIV');

            test.innerHTML = 'W';
            test.style.display = 'inline-block';
            line.style.width = '100%';
            bars.style.width = '100%';
            bars.style.height = '100%';
            bars.style.overflow = 'scroll';
            bars.style.position = 'absolute';

            this._editor.appendChild(test);
            this._editor.appendChild(line);
            this._facade.appendChild(bars);

            // Editor container and text offsets
            var editRect = this._facade.getBoundingClientRect(),
                charRect = test.getBoundingClientRect(),
                lineRect = line.getBoundingClientRect(),
                gutterRect = this._gutter.parentNode.getBoundingClientRect();

            // Char left and top
            this._charLeft = charRect.left;
            this._charTop = charRect.top;

            // Editor left and top
            this._editorLeft = editRect.left;
            this._editorTop = editRect.top;

            // Editor width and height
            this._editorWidth = editRect.width; // this._facade.offsetWidth;
            this._editorHeight = editRect.height; // this._facade.offsetHeight;

            // Gutter width
            this._gutterWidth = gutterRect.width; // this._gutter.parentNode.offsetWidth;

            // Character and line sizes
            this._charWidth = charRect.width; // test.clientWidth;
            this._charHeight = charRect.height; // test.clientHeight;
            this._lineWidth = lineRect.width - this._gutterWidth; // line.clientWidth - this._gutterWidth;
            this._pageSize = Math.ceil((editRect.height - this._facade.clientHeight + bars.clientHeight) / charRect.height);

            // Scrollbar width and height
            this._scrollWidth = this._facade.clientWidth - bars.clientWidth;
            this._scrollHeight = this._facade.clientHeight - bars.clientHeight;

            // Restore editor layout
            this._editor.removeChild(line);
            this._editor.removeChild(test);
            this._facade.removeChild(bars);
        },

        updateLayout: function()
        {
            this._scrollX.style.width = this._scrollWidth + 'px';
            this._scrollX.style.height = this._scrollHeight + 'px';

            this._scrollV.style.height = (this._editorHeight - this._scrollHeight) + 'px';
            this._scrollV.style.width = (this._scrollWidth) + 'px';
            this._scrollH.style.width = (this._editorWidth - this._scrollWidth) + 'px';
            this._scrollH.style.height = (this._scrollHeight) + 'px';

            this._offset.style.width = Math.max(this._editorWidth - this._scrollWidth, this.getLinesWidth() * this._charWidth + this._gutterWidth + 16) + 'px';
            this._offset.style.height = Math.max(this._editorHeight - this._scrollHeight, this._editorHeight - this._scrollHeight) + (2 * this._charHeight) + 'px';

            this._backer.parentNode.style.left = this._gutterWidth + 'px';
            this._marker.parentNode.style.left = this._gutterWidth + 'px';
            this._editor.parentNode.style.left = this._gutterWidth + 'px';
            this._cursor.parentNode.style.left = this._gutterWidth + 'px';
            this._gutter.parentNode.style.left = '0px';
        },

        pingCursor: function()
        {
            var self = this;

            // Function blinks cursor layer
            //
            var blink = function(e)
            {
                var hidden = self._cursor.style.opacity == '0';
                self._cursor.style.opacity = hidden ? '1' : '0';
            };

            // Fixes animation after specified timeout
            //
            var fixer = function(e)
            {
                self._cursor.className = 'animated';
                self._fixerTimeout = null;
            }

            this._cursor.className = null;
            this._cursor.style.opacity = '1';

            if (this._fixerTimeout)
                clearTimeout(this._fixerTimeout);

            if (this._blinkInterval)
                clearInterval(this._blinkInterval);

            this._blinkInterval = setInterval(blink, 500);
            this._fixerTimeout = setTimeout(fixer, 200);
        },

        stopCursor: function()
        {
            this._cursor.style.opacity = '1';

            if (this._blinkInterval)
                window.clearInterval(this._blinkInterval);
        },

        updateEditor: function(enforce, complete)
        {
            // Selfie?
            var self = this;

            // Prevent multiple calls
            if (!enforce)
            {
                var func = function() {self.updateEditor(true);};
                clearTimeout(this._updateEditorTimeout);
                this._updateEditorTimeout = setTimeout(func, 0);
                return;
            }

            // Collect reused elements
            var reused = [];

            // Get scrolling frame
            var frame = this.getScrollingFrame(true);

            // Creates editor node with text
            var createEditorNode = function(text)
            {
                var div = document.createElement('DIV');
                if (text) div.innerHTML = text;
                return div;
            };

            // Creates gutter node with text
            var createLineNumber = function(line)
            {
                if (self._enumLines === 'auto')
                    return line >= 0 && line < self.getLinesCount() ? line + 1 : '';

                if (self._enumLines === true)
                    return line + 1;

                return '';
            };

            // Function for adding selection block
            var createSelectNode = function(id, target, left, top, width, height)
            {
                var div = document.getElementById(id);

                if (!div)
                    div = target.appendChild(document.createElement('DIV'));

                reused.push(id);

                div.style.left = left + 'px';
                div.style.top = top + 'px';
                div.id = id;

                if (width) div.style.width = width + 'px';
                if (height) div.style.height = height + 'px';

                return div;
            };

            // Render selection, line highlights and cursors
            var lines = [], ranges = this.getSelectionRanges();

            // Loop through selection ranges
            for (var i = 0; i < ranges.length; i++)
            {
                var range = ranges[i];

                // Cursor and line highlight are only rendered if cursor line
                // fits into current frame
                if (frame.containsPoint(range.getStartPoint()))
                {
                    // Render cursor
                    createSelectNode
                    (
                        'c-' + range.id,
                        this._cursor,
                        this._charWidth * range.startPosition,
                        this._charHeight * (range.startLine - frame.topLine)
                    );

                    // Highlight lines with cursor
                    if (lines.indexOf(range.startLine) == -1)
                    {
                        lines.push(range.startLine);

                        createSelectNode
                        (
                            'b-' + range.id,
                            this._backer,
                            0,
                            this._charHeight * (range.startLine - frame.topLine)
                        );
                    }
                }

                // Signle-line selection
                if (frame.containsPoint(range.getStartPoint()) && !range.isMultiline() && !range.isCollapsed())
                {
                    createSelectNode
                    (
                        'm-s-' + range.id,
                        this._marker,
                        this._charWidth * Math.min(range.startPosition, range.endPosition),
                        this._charHeight * (range.startLine - frame.topLine),
                        this._charWidth * Math.abs(range.startPosition - range.endPosition)
                    );
                }

                // Multiline selection
                if (frame.collidesRange(range) && range.isMultiline() && !range.isCollapsed())
                {
                    var leftPoint = range.getLeftPoint(),
                        rightPoint = range.getRightPoint();

                    // Limit selection start
                    if (!range.containsPoint(leftPoint)) leftPoint = range.getLeftPoint();

                    // Limit selection end
                    if (!range.containsPoint(rightPoint)) rightPoint = range.getRightPoint();

                    // Draw starting block
                    if (range.getLinesCount() >= 2)
                    {
                        createSelectNode
                        (
                            'm-a-' + range.id,
                            this._marker,
                            this._charWidth * leftPoint.position,
                            this._charHeight * (leftPoint.line - frame.topLine),
                            this._lineWidth - this._charWidth * leftPoint.position + 16,
                            this._charHeight
                        );
                    }

                    // Draw middle block
                    if (range.getLinesCount() >= 3)
                    {
                        createSelectNode
                        (
                            'm-b-' + range.id,
                            this._marker,
                            0,
                            this._charHeight * (leftPoint.line - frame.topLine + 1),
                            null,
                            this._charHeight * (Math.abs(leftPoint.line - rightPoint.line) - 1)
                        );
                    }

                    // Draw ending block
                    if (rightPoint.position > 0)
                    {
                        createSelectNode
                        (
                            'm-c-' + range.id,
                            this._marker,
                            0,
                            this._charHeight * (rightPoint.line - frame.topLine),
                            this._charWidth * rightPoint.position,
                            this._charHeight
                        );
                    }
                }
            }

            // Remove unused elements
            var containers = [this._cursor, this._marker, this._backer];
            for (var j = 0; j < containers.length; j++)
                for (var i = containers[j].children.length - 1; i >= 0 ; i--)
                    if (reused.indexOf(containers[j].children[i].id) == -1)
                        containers[j].removeChild(containers[j].children[i]);

            // Rebuild editor
            if (!this._frame || this._frame.collidesFrame(frame) === 0 || complete)
            {
                while (this._editor.childNodes.length)
                    this._editor.removeChild(this._editor.firstChild);

                while (this._gutter.childNodes.length)
                    this._gutter.removeChild(this._gutter.firstChild);

                // Add lines
                for (var i = frame.topLine; i <= frame.endLine; i++)
                {
                    this._editor.insertBefore(createEditorNode(this.getTextAt(i)), null);
                    this._gutter.insertBefore(createEditorNode(createLineNumber(i)), null);
                }
            }

            // Scrolling down
            else if (this._frame.collidesFrame(frame) < 0)
            {
                // Remove lines from top and add lines to the end
                for (var i = 0; i < frame.topLine - this._frame.topLine; i++)
                {
                    this._editor.removeChild(this._editor.firstChild);
                    this._editor.insertBefore(createEditorNode(this.getTextAt(this._frame.endLine + 1 + i)), null);
                    this._gutter.removeChild(this._gutter.firstChild);
                    this._gutter.insertBefore(createEditorNode(createLineNumber(this._frame.endLine + 1 + i)), null);
                }
            }

            // Scrolling up
            else if (this._frame.collidesFrame(frame) > 0)
            {
                // Remove lines from end and add lines to the top
                for (var i = 0; i < this._frame.topLine - frame.topLine; i++)
                {
                    this._editor.removeChild(this._editor.lastChild);
                    this._editor.insertBefore(createEditorNode(this.getTextAt(this._frame.topLine - 1 - i)), this._editor.firstChild);
                    this._gutter.removeChild(this._gutter.lastChild);
                    this._gutter.insertBefore(createEditorNode(createLineNumber(this._frame.topLine - 1 - i)), this._gutter.firstChild);
                }
            }

            // Set vertical scroll offset
            this._offset.style.marginTop = '-' + (this._scrollV.scrollTop - this._charHeight * frame.topLine) + 'px';

            // Set horizontal scroll offset
            this._offset.style.marginLeft = '-' + (this._scrollH.scrollLeft) + 'px';

            // Set gutter offset
            this._gutter.parentNode.style.left = (this._scrollH.scrollLeft) + 'px';

            // Set vertical scroll height
            this._expandV.style.height = (this.getLinesCount() * this._charHeight) + 'px';

            // Set horizontal scroll width
            this._expandH.style.width = ((this.getLinesWidth() + 2) * this._charWidth + this._gutterWidth) + 'px';

            // Set vertical scroll span width
            this._expandV.style.width = this._scrollWidth + 'px';

            // Set horizontal scroll span height
            this._expandH.style.height = this._scrollHeight +'px';

            // Update scroll vars
            this._frame = frame;
        },

        updateScroll: function(key)
        {
            // Selection ranges
            var ranges = this.getSelectionRanges();

            // Calculate visible frame borders
            var frame = this.getScrollingFrame();

            // Get first cursor
            var caret = ranges[0];

            // Find the last cursor in a visible frame
            if (key)
            {
                for (var i = 1; i < ranges.length; i++)
                {
                    var range = ranges[i];

                    // Does cursor fit in a visible frame?
                    if (frame.containsPoint(range.getStartPoint()))
                    {
                        if (key === '[up]' && caret.startLine < range.startLine) caret = range;
                        if (key === '[down]' && caret.startLine > range.startLine) caret = range;
                        if (key === '[left]' && caret.startPosition < range.startPosition) caret = range;
                        if (key === '[right]' && caret.startPosition > range.startPosition) caret = range;
                    }
                }
            }

            // Update view frame scrolling position
            if (caret)
            {
                if (caret.startLine <= frame.topLine)
                    this._scrollV.scrollTop = caret.startLine * this._charHeight;

                if (caret.startLine >= frame.endLine)
                    this._scrollV.scrollTop = (caret.startLine - frame.endLine + frame.topLine) * this._charHeight + this._scrollHeight;

                if (caret.startPosition < frame.topPosition)
                    this._scrollH.scrollLeft = caret.startPosition * this._charWidth;

                if (caret.startPosition > frame.endPosition)
                    this._scrollH.scrollLeft = (caret.startPosition - frame.endPosition + frame.topPosition) * this._charWidth;
            }

        },

        attachHandlers: function()
        {
            var self = this;

            this._offset.addEventListener('DOMMouseScroll', function(e) {return self.handleMouseWheel(e);}, true);
            this._offset.addEventListener('mousewheel', function(e) {return self.handleMouseWheel(e);}, true);

            this._offset.addEventListener('mousedown', function(e) {return self.handleMouseEvent(e);}, true);
            this._window.addEventListener('mouseup', function(e) {return self.handleMouseEvent(e);}, true);
            this._window.addEventListener('mousemove', function(e) {return self.handleMouseEvent(e);}, true);

            this._scrollV.addEventListener('scroll', function(e) {return self.handleScroll(e);}, true);
            this._scrollH.addEventListener('scroll', function(e) {return self.handleScroll(e);}, true);

            this._facade.addEventListener('keydown', function(e) {return self.handleKeyEvent(e);}, true);
            this._facade.addEventListener('keypress', function(e) {return self.handleKeyEvent(e);}, true);

            this._facade.addEventListener('focus', function(e) {return self.handleFocus(e);}, true);
            this._facade.addEventListener('blur', function(e) {return self.handleBlur(e);}, true);
        },

        handleScroll: function(e)
        {
            this.updateEditor(true);

            return e.preventDefault() && false;
        },

        handleMouseWheel: function(e)
        {
            var delta = e.wheelDelta ? e.wheelDelta / 40 : e.detail ? -e.detail : 0;

            this._scrollV.scrollTop -= delta * this._charHeight;

            this.updateEditor(true);

            return e.preventDefault() && false;
        },

        handleMouseEvent: function(e)
        {
            // Create mouse event point
            var point = this.getMousePosition(e);

            // Get last selection range
            var range = this.getSelectionRange();

            // Handle mousedown
            if (e.type === 'mousedown')
            {
                // Event timestamp (for dblclick)
                var timestamp = Date.now();

                // Return focus to editor
                this.getFacade().focus();

                // Mark dragging
                this._dragging = true;

                // Remember selection
                this._dragSelection = this._selection.slice(0);

                // Double click
                var doubleClick = this._doubleClick &&
                    timestamp - this._doubleClick.timestamp <= this._doubleClickTime &&
                    this._doubleClick.x == e.clientX && this._doubleClick.y == e.clientY;

                var tripleClick = this._tripleClick &&
                    timestamp - this._tripleClick.timestamp <= this._doubleClickTime &&
                    this._tripleClick.x == e.clientX && this._tripleClick.y == e.clientY;

                // Single click
                if (!doubleClick && !tripleClick)
                {
                    this._doubleClick = {timestamp: timestamp, x: e.clientX, y: e.clientY};

                    // Click on gutter
                    if (e.target.parentNode === this._gutter)
                        this.addSelectionRange(point.line, 0, point.line, 0, !e.ctrlKey);

                    // Shift-click selection from cursor to new click position
                    else if (e.shiftKey)
                        range.expandBy(point);

                    // Reset selection if no control is held
                    else
                        this.addSelectionRange(point.line, point.position, point.line, point.position, !e.ctrlKey);
                }

                // Double-click select word
                else if (doubleClick)
                {
                    // Click on gutter
                    if (e.target.parentNode === this._gutter)
                    {
                        this._lineRange = this.createLineSelectionRange(point.line);
                        this.addSelectionRange(this._lineRange, !e.ctrlKey);
                    }

                    // Select word on double click
                    else
                    {
                        this._tripleClick = {timestamp: timestamp, x: e.clientX, y: e.clientY};
                        this._doubleClick = null;

                        this._wordRange = this.createWordSelectionRange(point.line, point.position);
                        this.addSelectionRange(this._wordRange, !e.ctrlKey);
                    }
                }

                // Triple click select line
                else if (tripleClick)
                {
                    this._tripleClick = null;

                    this._lineRange = this.createLineSelectionRange(point.line);
                    this.addSelectionRange(this._lineRange, !e.ctrlKey);
                }

                this.normalizeSelection();
                this.updateScroll();
                this.updateEditor(true);
                this.pingCursor();
            }

            // Handle mousemove
            if (e.type === 'mousemove')
            {
                if (this._dragging)
                {
                    // Word mode selection
                    if (this._wordRange)
                    {
                        var copyRange = this._wordRange.clone(),
                            wordRange = this.createWordSelectionRange(point.line, point.position, true);

                        copyRange.expandBy(wordRange.getStartPoint());

                        this.addSelectionRange(copyRange, true);
                    }

                    // Line selection
                    else if (this._lineRange)
                    {
                        var copyRange = this._lineRange.clone(),
                            lineRange = this.createLineSelectionRange(point.line, true);

                        copyRange.expandBy(lineRange.getStartPoint());

                        this.addSelectionRange(copyRange, true);
                    }

                    // Normal drag selection
                    else
                    {
                        range.startLine = point.line;
                        range.startPosition = point.position;
                        range.fixedPosition = point.position;

                        if (e.ctrlKey)
                        {
                            this._selection = this._dragSelection.slice(0);
                            this._selection.push(range);
                        }
                    }

                    this.normalizeSelection();
                    this.updateScroll();
                    this.updateEditor(true);
                    this.pingCursor();
                }
            }

            // Handle mouseup
            if (e.type === 'mouseup')
            {
                this._dragging = false;
                this._dragSelection = null;
                this._wordRange = null;
                this._lineRange = null;
            }

            return e.preventDefault() && false;
        },

        handleKeyEvent: function(e)
        {
            var ranges = this.getSelectionRanges(),
                key = this.getPressedKey(e),
                handled = true;

            // Whether all ranges are collapsed
            var collapsed = true;
            for (var i = 0; i < this._selection.length; i++)
                collapsed = collapsed && this._selection[i].isCollapsed();

            // And we do this for every cursor
            for (var i = 0; i < ranges.length; i++)
            {
                var range = ranges[i];

                // Printing characters
                if (key.length === 1 && !e.ctrlKey && e.type === 'keypress')
                {
                    if (!collapsed)
                        this.removeTextAt(range);

                    this.insertTextAt(range, String.fromCharCode(e.charCode));
                }

                else if (key === '[enter]')
                {
                    if (!collapsed)
                        this.removeTextAt(range);

                    this.insertLineAt(range, '');
                }

                else if (key === '[delete]')
                {
                    if (e.ctrlKey)
                        this.moveCursorBy(range, FireCode.WORD, +1, true);
                    else if (collapsed)
                        this.moveCursorBy(range, FireCode.CHAR, +1, true);

                    this.removeTextAt(range);
                }

                else if (key === '[backspace]')
                {
                    if (e.ctrlKey)
                        this.moveCursorBy(range, FireCode.WORD, -1, true);
                    else if (collapsed)
                        this.moveCursorBy(range, FireCode.CHAR, -1, true);

                    this.removeTextAt(range);
                }

                else if (key === '[tab]' && !range.isCollapsed())
                    this.indentText(range, e.shiftKey ? -1 : +1);

                else if (key === '[tab]')
                    this.insertTextAt(range, Array(this._tabSize + 1).join(' '));

                else if (key === 'A' && e.ctrlKey)
                    this.selectAll();

                else if (key === 'D' && e.ctrlKey)
                    this.duplicateLine(range);

                else if (key === '[left]' && e.ctrlKey)
                    this.moveCursorBy(range, FireCode.WORD, -1, e.shiftKey);

                else if (key === '[right]' && e.ctrlKey)
                    this.moveCursorBy(range, FireCode.WORD, +1, e.shiftKey);

                else if (key === '[left]')
                    this.moveCursorBy(range, FireCode.CHAR, -1, e.shiftKey);

                else if (key === '[right]')
                    this.moveCursorBy(range, FireCode.CHAR, +1, e.shiftKey);

                else if (key === '[up]' && e.ctrlKey)
                    this.scrollFrameBy(-1);

                else if (key === '[down]' && e.ctrlKey)
                    this.scrollFrameBy(+1);

                else if (key === '[up]')
                    this.moveCursorBy(range, FireCode.LINE, -1, e.shiftKey);

                else if (key === '[down]')
                    this.moveCursorBy(range, FireCode.LINE, +1, e.shiftKey);

                else if (key === '[pageup]')
                    this.moveCursorBy(range, FireCode.PAGE, -1, e.shiftKey);

                else if (key === '[pagedown]')
                    this.moveCursorBy(range, FireCode.PAGE, +1, e.shiftKey);

                else if (key === '[home]')
                    this.moveCursorTo(range, FireCode.BOL, e.shiftKey);

                else if (key === '[end]')
                    this.moveCursorTo(range, FireCode.EOL, e.shiftKey);

                else
                    handled = false;
            }

            if (handled)
            {
                this.pingCursor();
                this.normalizeSelection();
                this.updateScroll(key);
                this.updateEditor(true, true);
                return e.preventDefault() && false;
            }

            return true;
        },

        handleFocus: function(e)
        {
            this.pingCursor();
        },

        handleBlur: function(e)
        {
            this.stopCursor();
        },

        getPressedKey: function(e)
        {
            var keys =
            {
                9 : '[tab]',
                8 : '[backspace]',
                33 : '[pageup]',
                34 : '[pagedown]',
                35 : '[end]',
                36 : '[home]',
                27 : '[esc]',
                46 : '[delete]',
                13 : '[enter]',
                37 : '[left]',
                38 : '[up]',
                39 : '[right]',
                40 : '[down]'
            };

            return keys[e.keyCode] ?
                keys[e.keyCode] :
                String.fromCharCode(e.charCode).toUpperCase();
        },

        getMousePosition: function(e)
        {
            var x = e.pageX - this._charLeft - this._gutterWidth + this._scrollH.scrollLeft + 2,
                y = e.pageY - this._charTop + this._scrollV.scrollTop - this._charHeight * this._frame.topLine,
                line = Math.floor(y / this._charHeight) + this._frame.topLine,
                position = Math.floor(x / this._charWidth);

            // Limit line number
            var maxLine = this.getLinesCount() - 1;
            if (line > maxLine) line = maxLine;
            if (line < 0) line = 0;

            // Limit cursor position
            var maxPosition = this.getLineLength(line);
            if (position > maxPosition) position = maxPosition;
            if (position < 0) position = 0;

            return new FireCode.SelectionPoint(line, position);
        },

        getScrollingFrame: function(verticalOnly)
        {
            var minLine = Math.floor(this._scrollV.scrollTop / this._charHeight),
                maxLine = minLine + Math.ceil((this._scrollV.offsetHeight - this._scrollHeight) / this._charHeight),
                minPosition = verticalOnly ? null : Math.floor(this._scrollH.scrollLeft / this._charWidth),
                maxPosition = verticalOnly ? null : minPosition + Math.floor((this._scrollH.offsetWidth - this._gutterWidth - this._scrollWidth) / this._charWidth);

            return new FireCode.ScrollingFrame(minLine, minPosition, maxLine, maxPosition);
        },

        getText: function()
        {
            return this._lines.join("\n");
        },

        setText: function(text)
        {
            this._lines = text.split("\n");

            // Update lines width
            this._lineSize = 0;
            for (var i = 0; i < this._lines.length; i++)
                if (this._lines[i].length > this._lineSize)
                    this._lineSize = this._lines[i].length;
        },

        getTextAt: function(line, position, length)
        {
            if (!this._lines[line])
                return '';

            else if (line != undefined && position == undefined && length == undefined)
                return this._lines[line];

            else if (line != undefined && position != undefined && length == undefined)
                return this._lines[line].substr(position);

            else if (line != undefined && position != undefined && length >= 0)
                return this._lines[line].substr(position, length);

            else if (line != undefined && position != undefined && length < 0)
                return this._lines[line].substr(position + length, -length);
        },

        getLineText: function(line)
        {
            if (line != undefined && this._lines[line])
                return this._lines[line];
        },

        setLineText: function(line, text)
        {
            if (line != undefined && text != undefined && this._lines[line])
                return this._lines[line] = text;
        },

        getLineLength: function(line)
        {
            return this._lines[line] ? this._lines[line].length : 0;
        },

        getLinesCount: function()
        {
            return this._lines.length;
        },

        getLinesWidth: function()
        {
            return this._lineSize;
        },

        getSelectionRanges: function()
        {
            return this._selection;
        },

        clearSelection: function()
        {
            this._selection.length = 0;
        },

        getSelectionRange: function(index)
        {
            return this._selection[index ? index : this._selection.length - 1];
        },

        addSelectionRange: function(/* startLine, startPosition, endLine, endPosition, clearSelection */)
        {
            var range = null, clear = false;

            if (arguments[0] instanceof FireCode.SelectionRange)
            {
                range = arguments[0];
                clear = arguments[1];
            }

            else
            {
                range = new FireCode.SelectionRange(arguments[0], arguments[1], arguments[2], arguments[3]);
                clear = arguments[4];
            }

            if (clear) this._selection.length = 0;

            if (range) this._selection.push(range);

            return range;
        },

        createLineSelectionRange: function(line, extend)
        {
            var maxPosition = this.getLineLength(line),
                linesCount = this.getLinesCount();

            if (extend)
                return new FireCode.SelectionRange(line, 0, line, maxPosition);

            else if (line + 1 >= linesCount)
                return new FireCode.SelectionRange(line, maxPosition, line, 0);

            else
                return new FireCode.SelectionRange(line + 1, 0, line, 0);
        },

        createWordSelectionRange: function(line, position, extend)
        {
            var text = this.getTextAt(line),
                chars = this._wordSeparators,
                startLine = line,
                startPosition = null,
                endLine = line,
                endPosition = null;

            var halfWord = text.substr(position).match(/^(\s+|[^\w\s]+|\w+)/);
                halfWord = halfWord ? halfWord[0] : '';

            var fullWord = text.substr(0, position + halfWord.length).match(/([^\w\s]+|\w+|\s+)$/);
                fullWord = fullWord ? fullWord[0] : '';

            if (halfWord.length > fullWord.length - halfWord.length/* && fullWord.length > 1*/)
            {
                startPosition = position + halfWord.length - fullWord.length;
                endPosition = position + halfWord.length;
            }
            else
            {
                startPosition = position + halfWord.length;
                endPosition = position + halfWord.length - fullWord.length;
            }

            return new FireCode.SelectionRange(startLine, startPosition, endLine, endPosition);
        },

        createFileSelectionRange: function()
        {
            var line = this.getLinesCount() - 1,
                position = this.getLineLength(line);

            return new FireCode.SelectionRange(line, position, 0, 0);
        },

        createSelectionPoint: function(range, type)
        {
            var line;

            if (type === FireCode.BOF)
                return new FireCode.SelectionPoint(0, 0);

            if (type === FireCode.EOF)
                return new FireCode.SelectionPoint(line = this.getLinesCount() - 1, this.getLineLength(line));

            if (type === FireCode.BOL)
            {
                if (this._smartHome)
                {
                    var lineText = this.getTextAt(range.startLine),
                        basePosition = 0,
                        textPosition = lineText.search(/[^\s]/);

                    if (textPosition == -1)
                        textPosition = 0;

                    if (range.startPosition === textPosition)
                        return new FireCode.SelectionPoint(range.startLine, basePosition);

                    else
                        return new FireCode.SelectionPoint(range.startLine, textPosition);
                }
                else
                    return new FireCode.SelectionPoint(range.startLine, 0);
            }

            if (type === FireCode.EOL)
                return new FireCode.SelectionPoint(line = range.startLine, this.getLineLength(line));
        },

        normalizeSelection: function()
        {
            do
            {
                var mergeCount = 0;

                // Merge selection ranges
                for (var i = 0; i < this._selection.length - 1 && this._selection.length > 1; i++)
                {
                    for (var j = i + 1; j < this._selection.length; j++)
                    {
                        var rangeOne = this._selection[i],
                            rangeTwo = this._selection[j],
                            oneStartPoint = rangeOne.getStartPoint(),
                            twoStartPoint = rangeTwo.getStartPoint(),
                            oneEndPoint = rangeOne.getEndPoint(),
                            twoEndPoint = rangeTwo.getEndPoint(),
                            oneLeftPoint = rangeOne.getLeftPoint(),
                            twoLeftPoint = rangeTwo.getLeftPoint(),
                            oneRightPoint = rangeOne.getRightPoint(),
                            twoRightPoint = rangeTwo.getRightPoint(),
                            oneCollapsed = rangeOne.isCollapsed(),
                            twoCollapsed = rangeTwo.isCollapsed();

                        // Both ranges are collapsed and equal
                        if (oneCollapsed && rangeOne.equalsTo(rangeTwo))
                        {
                            this._selection.splice(j, 1);
                            mergeCount++;
                        }
                        // Range one (cursor) belongs to range two
                        else if (oneCollapsed && !twoCollapsed && rangeTwo.containsPoint(oneStartPoint, true))
                        {
                            this._selection.splice(i, 1);
                            mergeCount++;
                        }

                        // Range two (cursor) belongs to range one
                        else if (twoCollapsed && !oneCollapsed && rangeOne.containsPoint(twoStartPoint, true))
                        {
                            this._selection.splice(j, 1);
                            mergeCount++;
                        }


                        // Range one (with text) contains range two
                        else if (!oneCollapsed && !twoCollapsed &&
                                rangeOne.containsPoint(twoStartPoint) &&
                                rangeOne.containsPoint(twoEndPoint))
                        {
                            this._selection.splice(j, 1);
                            mergeCount++;
                        }

                        // Range two (with text) contains range one
                        else if (!oneCollapsed && !twoCollapsed &&
                                rangeTwo.containsPoint(oneStartPoint) &&
                                rangeTwo.containsPoint(oneEndPoint))
                        {
                            this._selection.splice(i, 1);
                            mergeCount++;
                        }

                        // Range two intersects into range one
                        else if (!oneCollapsed && !twoCollapsed && rangeOne.containsPoint(twoStartPoint, true))
                        {
                            var point = null;

                            if (twoEndPoint.compareTo(oneStartPoint) > 0 &&
                                twoEndPoint.compareTo(oneEndPoint) > 0)
                                point = oneLeftPoint;
                            else
                                point = oneRightPoint;

                            var range = rangeTwo.clone();
                            range.expandBy(point);

                            this._selection.splice(i, 1, range);
                            this._selection.splice(j, 1);

                            mergeCount++;
                        }

                        // Range one intersects into range two
                        else if (!oneCollapsed && !twoCollapsed && rangeTwo.containsPoint(oneStartPoint, true))
                        {
                            var point = null;

                            if (oneEndPoint.compareTo(twoStartPoint) > 0 &&
                                oneEndPoint.compareTo(twoEndPoint) > 0)
                                point = twoLeftPoint;
                            else
                                point = twoRightPoint;

                            var range = rangeOne.clone();
                            range.expandBy(point);

                            this._selection.splice(i, 1, range);
                            this._selection.splice(j, 1);

                            mergeCount++;
                        }
                    }
                }
            }

            // Repeat until everything is merged
            while(mergeCount > 0);
        },

        removeTextAt: function(range)
        {
            if (!range.isCollapsed())
            {
                var leftPoint = range.getLeftPoint(),
                    rightPoint = range.getRightPoint(),
                    leftText = this.getTextAt(leftPoint.line, 0, leftPoint.position),
                    rightText = this.getTextAt(rightPoint.line, rightPoint.position),
                    lineShift = range.getLinesCount() - 1,
                    positionShift = leftPoint.position - rightPoint.position;

                // Update other ranges
                // Here we imply there are no errors in the code and
                // selection doesn't contain any other ranges
                for (var i = 0; i < this._selection.length; i++)
                {
                    var fixRange = this._selection[i];

                    if (range !== fixRange)
                    {
                        if (lineShift > 0 && fixRange.getLeftPoint().compareTo(rightPoint) >= 0)
                        {
                            fixRange.startLine -= lineShift;
                            fixRange.endLine -= lineShift;
                        }

                        if (positionShift !== 0 && fixRange.getStartPoint().compareTo(rightPoint) >= 0 && fixRange.startLine === rightPoint.line)
                            fixRange.startPosition += positionShift;

                        if (positionShift !== 0 && fixRange.getEndPoint().compareTo(rightPoint) >= 0 && fixRange.endLine === rightPoint.line)
                            fixRange.endPosition += positionShift;
                    }
                }

                // Replace line(s)
                this._lines.splice(leftPoint.line, range.getLinesCount(), leftText + rightText);

                // Collapse range to left point
                range.collapseTo(leftPoint);
            }
        },

        insertTextAt: function(range, text)
        {
            if (range.isCollapsed())
            {
                var startPoint = range.getStartPoint(),
                    leftText = this.getTextAt(range.startLine, 0, range.startPosition),
                    rightText = this.getTextAt(range.startLine, range.startPosition),
                    spaceText = this._smartEnter ? leftText.match(/^\s*/g)[0] : '',
                    positionShift = text.length - range.startPosition + spaceText.length;

                // Replace line(s)
                this._lines.splice(range.startLine, 1, leftText + text + rightText);

                // Update other ranges
                for (var i = 0; i < this._selection.length; i++)
                {
                    var fixRange = this._selection[i];

                    if (range !== fixRange)
                    {
                        if (positionShift !== 0 && fixRange.getStartPoint().compareTo(startPoint) >= 0 && fixRange.startLine === startPoint.line)
                            fixRange.startPosition += text.length;

                        if (positionShift !== 0 && fixRange.getEndPoint().compareTo(startPoint) >= 0 && fixRange.endLine === startPoint.line)
                            fixRange.endPosition += text.length;
                    }
                }

                // Move cursor right
                var point = new FireCode.SelectionPoint(range.startLine, range.startPosition + text.length);
                range.collapseTo(point);
            }
        },

        insertLineAt: function(range, text)
        {
            if (range.isCollapsed())
            {
                var startPoint = range.getStartPoint(),
                    leftText = this.getTextAt(range.startLine, 0, range.startPosition),
                    rightText = this.getTextAt(range.startLine, range.startPosition),
                    spaceText = this._smartEnter ? leftText.match(/^\s*/g)[0] : '',
                    positionShift = text.length - range.startPosition + spaceText.length;

                // Replace source line
                this._lines.splice(startPoint.line, 1, leftText + text);

                // Add new line
                this._lines.splice(startPoint.line + 1, 0, spaceText + rightText);

                // Update other ranges
                for (var i = 0; i < this._selection.length; i++)
                {
                    var fixRange = this._selection[i];

                    if (range !== fixRange)
                    {
                        if (fixRange.getLeftPoint().compareTo(startPoint) >= 0)
                        {
                            fixRange.startLine += 1;
                            fixRange.endLine += 1;
                        }

                        if (positionShift !== 0 && fixRange.getStartPoint().compareTo(startPoint) >= 0 && fixRange.startLine === startPoint.line)
                            fixRange.startPosition += positionShift;

                        if (positionShift !== 0 && fixRange.getEndPoint().compareTo(startPoint) >= 0 && fixRange.endLine === startPoint.line)
                            fixRange.endPosition += positionShift;
                    }
                }

                // Move cursor
                var point = new FireCode.SelectionPoint(range.startLine + 1, spaceText.length + text.length);
                range.collapseTo(point);
            }

        },

        indentText: function(range, shift)
        {
            var topLine = Math.min(range.startLine, range.endLine);
            var endLine = Math.max(range.startLine, range.endLine);
            var reverted = range.startLine > range.endLine;

            if (shift > 0)
            {
                var insert = new Array(shift * this._tabSize + 1).join(' ');
                for (var line = topLine; line <= endLine; line++)
                    this.setLineText(line, insert + this.getLineText(line));
                range.startPosition += shift * this._tabSize;
                range.endPosition += shift * this._tabSize;
            }

            if (shift < 0)
            {
                var regexp = new RegExp('^ {0,' + (-1 * shift * this._tabSize) + '}');
                for (var line = topLine; line <= endLine; line++)
                {
                    var text = this.getLineText(line);
                    var spacing = text.match(regexp);
                        spacing = spacing ? spacing[0] : '';

                    this.setLineText(line, text.substr(spacing.length));

                    if (line === topLine) reverted ? range.endPosition -= spacing.length : range.startPosition -= spacing.length;
                    if (line === endLine) reverted ? range.startPosition -= spacing.length : range.endPosition -= spacing.length;
                }

            }
        },

        moveCursor: function(range, line, position, extend)
        {
            var minLine = 0, maxLine = this.getLinesCount() - 1;
            if (line < minLine) line = minLine;
            else if (line > maxLine) line = maxLine;

            var minPosition = 0, maxPosition = this.getLineLength(line);
            if (position < minPosition) position = minPosition;
            else if (position > maxPosition) position = maxPosition;

            range.startLine = line;
            range.startPosition = position;
            range.fixedPosition = position;

            if (!extend)
            {
                range.endLine = line;
                range.endPosition = position;
            }
        },

        moveCursorTo: function(range, type, extend)
        {
            var point = this.createSelectionPoint(range, type);
            if (extend) range.extendTo(point); else range.collapseTo(point);
        },

        moveCursorBy: function(range, type, shift, extend)
        {
            var line = range.startLine,
                position = range.startPosition;

            // Page up / down
            if (type === FireCode.PAGE)
            {
                var minLine = 0, maxLine = this.getLinesCount() - 1;

                line += shift * this._pageSize;

                if (line < minLine) line = minLine;
                else if (line > maxLine) line = maxLine;

                position = Math.min(range.fixedPosition, this.getLineLength(line));
            }

            // Line up / down
            if (type === FireCode.LINE)
            {
                var minLine = 0, maxLine = this.getLinesCount() - 1;

                line += shift;

                if (line < minLine) line = minLine;
                else if (line > maxLine) line = maxLine;

                position = Math.min(range.fixedPosition, this.getLineLength(line));
            }

            // Word navigation
            if (type === FireCode.WORD)
            {
                var lineText = this.getTextAt(line),
                    maxPosition = this.getLineLength(line);

                if (shift < 0)
                {
                    if (position == 0 && line > 0) {line--; position = this.getLineLength(line);}
                    else position -= lineText.substr(0, position).match(/([\w]*|[^\w\s]*)\s*$/)[0].length;
                }

                if (shift > 0)
                {
                    if (position == this.getLineLength(line) && line < this.getLinesCount() - 1) {line++; position = 0;}
                    else position += lineText.substr(position).match(/^([^\w\s]+|[\w]*)\s*/)[0].length;
                }
            }

            // Char navigation
            if (type === FireCode.CHAR)
            {
                var minLine = 0, maxLine = this.getLinesCount() - 1;

                while (shift > 0)
                {
                    shift--;
                    if (position >= this.getLineLength(line) && line < maxLine)
                    {
                        line++;
                        position = 0;
                    }
                    else
                        position++;
                }

                while (shift < 0)
                {
                    shift++;
                    if (position === 0 && line > 0)
                    {
                        line--;
                        position = this.getLineLength(line);
                    }
                    else if (position > 0)
                        position--;
                }

            }

            range.startLine = line;
            range.startPosition = position;
            range.fixedPosition = position;

            if (!extend)
            {
                range.endLine = line;
                range.endPosition = position;
            }
        },

        scrollFrameBy: function(lineShift, positionShift)
        {
            var scrollLine = Math.floor(this._scrollV.scrollTop / this._charHeight),
                scrollPosition = Math.floor(this._scrollH.scrollLeft / this._charWidth);

            if (lineShift)
                this._scrollV.scrollTop = this._charHeight * (scrollLine + lineShift);

            if (positionShift)
                this._scrollH.scrollLeft = this._charWidth * (scrollPosition + positionShift);
        },

        selectAll: function()
        {
            this.clearSelection();
            this.addSelectionRange(this.createFileSelectionRange());
        },

        duplicateLine: function(range)
        {
            var newLineRange = range.clone();

            newLineRange.startPosition = 0;
            newLineRange.endPosition = 0;

            this.insertLineAt(newLineRange, this.getTextAt(range.startLine));
        }

    });

})(window, document);