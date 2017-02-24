
- Options
    - Cursor blinking interval
    - Revert animation timeout
    - Animate cursor movements while typing
    - Bound/unbound cursor (and freespace typing)
    - Bound/unbound selection rendering
    - Hide or fade caret on lose focus
    - Expand editor height or scroll (min/max)
    - Horizontal scrolbar overlap gutter or not
    - Show or hide gutter + runtime switch
    - Scrollbar behavior: show / hide / auto
    - Number of prerendered hidden lines
    - Whether to allow scroll down to space
    - Smooth or per-line scrolling
    - Visible frame paddings (auto-scroll)
    - Cursor position in word-click selections

- [BIG] Clean editor API
  - getLineNode(index)
  - getLineText()
  - getFocusNode()
  - getAnchorNode()
  - getSelection()
  - getCursorPosition()
- [BIG] Copy/Paste + Multiline
- [BIG] Change history and Undo/Redo
  - Persist cursor movements
  - Group similar actions (option)
- [BIG] Implement PHP/HTML/CSS/JS context
  - Auto-closing brackets and quotes [], {}, (), "", ''
  - Bypass typing closing brackets and quotes
- [BIG] Autocomplete popup window
- [BIG] Snippets with placeholders
+ [BIG] Selection on mouse dragging
- [BIG] Code folding
- [BIG] Big files support

===================================================================================================

- Method for text replace()
- Gutter highlight
- Prevent line numbers selection
- Implement more cursor styles
+ Block cursor
+ Smooth blinking
- Replace textarea element(s)
- Replace any tag + load remote file
- Automatic language detection
- Create language plugin system

- Function arguments tooltips
- Function help with Ctrl + Click

- Goto line with Ctrl + G
- Duplicate selection with Ctrl + D
- Bookmarks with Ctrl + [Shift] + [0-9]
- Highligh matching brackets
- Highligh matching variables
- Toggle autocomplete with Ctrl + Space
- Toggle snippets with Ctrl + J

- Base class for plugins (e.g. function tooltips)
- Single file for the whole editor (using Gulp)
- Support multiple simultaneous editors

+ Switch between line home / content start with Home
- Select line with double click on gutter
- Select token with double click
- Multiple cursors

- Context menu
- Modal windows
- Status line
- Open file tabs
- Project tree

- loadCode, saveCode() - ctrl + S
- Implement Cut/Copy/Paste
- Embed CSS into JS source
- Minify and compress with GCC
- Fix GCC errors and warnings
- Complete test coverage, QUnit
- Separate class Cursor/SelectionRange
- Cursor duplicating ctrl + alt + up/down

===================================================================================================

+ Use DIV instead of an IFRAME
+ Refactor code to use prototypes
+ Use base64-encoded inline images
+ Display gutter with line numbers
+ Gutter should float on h-scrolling
+ Deselect when clicking on gutter
+ Per-line text blocks organization
+ Highlight currently edited line
+ Emulate cursor with inline block
+ Blink cursor
+ Hide cursor on blur
+ Show cursor on focus
+ Should follow selection
+ Move cursor with Up/Down/PgUp/PgDown
+ Select all text with Ctrl + A
+ Duplicating line with Ctrl + D
+ Remove line with Ctrl + Y
+ Move cursor with Ctrl + Left/Right
+ Move cursor with Home / End
+ Selection with Ctrl + Shift + Left/Right
+ Remove content with Ctrl + Backspace
+ Selection indent with [selection] + Tab
+ Selection unindent [selection] + Shift + Tab
+ Scroll to cursor with Ctrl + Shift + Left/Right
+ Viewport scrolling with Ctrl + Up/Down
+ Snap cursor to grid on tabulation (4x spaces)
+ Persist line indentation on pressing Enter
+ Persist cursor position on pressing Up/Down
+ Scroll on Backspace/Enter at the end of file

+ Detect line height automatically
+ Update gutter line numbers
+ Implement multiline range support
+ Highlight line with cursor
+ Editor focus management
+ Click to set cursor
+ Mouse selection
+ Smooth blinking cursor
+ Use 0-indices for lines and positions

+ Methods pingCaret, hideCaret
+ Text modification methods
+ File parser
+ Detect scrolbar width
+ Split get/setLineText()
+ Do not use gutter offset
+ Auto-tune editor layout
+ Custom scrolling
+ Support mousewheel
+ Fix duplicating lines bug
+ Text block should not be covered by scroll bar
+ Implement horizontal scrolling
+ Implement floating gutter
+ Rename select -> marker
+ Separate backer DIV
+ updateEditor()
  + Text rendering
  + Gutter rendering
  + Reuse DIVs
  + Line highlight rendering
  + Selection rendering
  + Cursor rendering
+ Single closure with self
+ Attach/detach methods
+ Click to text position
+ Click shanges selection
+ Ctrl + click adds cursor
+ Do not add cursor at the same position
+ Dedicated coordinates function
+ Mouse drag selection
+ Shift + click selection
+ Do not hardcode highlight width
+ Fix gutter rendering
+ Do not hardcode selection width
+ Separate getMetrics() function
+ Error getLineText() is null
+ Optimize selection
+ Do not hardcode gutter width
+ One function for all blocks
+ Scrollbar width bug
+ Initialize cursor on start
+ getMousePosition(e)
+ getPressedKey(e)
+ Simplify rendering functions
+ Bypass unhandled keyboard events
+ Fade cursor on lose focus
+ Lose focus should not detach keyhandler
+ Selection flickers on scroll
+ Errors moving cursor between lines
+ Fix Internet Explorer
+ Save selection position
+ Scroll into view when moving cursors
+ Bug moving two cursors
+ Bug with the longest line
+ Bug with selection
+ Scroll into view on mouse drag selection
+ Per-word navigation ctrl + left/right
+ Bug with last selection line
+ Frame scroll ctrl + up/down
+ Selection with Shift + up/dn/left/right
+ Double-click select word

+ SelectionRange
  + Fix constructor (support collapsed ranges)
  + clearSelection()
  + getSelectionRanges()
  + addSelectionRange(startLine, startPosition, endLine, endPosition)
  + addWordSelectionRange(line, position)
  + addLineSelectionRange(line)
+ FireCode.SelectionPoint
+ FireCode.SelectionRange
  + getLinesCount()
  + copyFrom(range)
  + extendTo() - should update fixedPosition
  + addWordSelectionRange() - should clear selection (3rd parameter)
  + addLineSelectionRange() - should clear selection (3rd parameter)
  + addSelectionRange() - add 5th argument replaceAll
+ FireCode.ScrollingFrame
  + collidesFrame(frame)
  + collidesRange(range)
  + containsPoint(point)
  + getTopPoint()
  + getEndPoint()
+ Rewrite updateEditor()
  + Bug click-word only works on the first line
  + Fix selection disappear
  + Gutter rendering error
  + Fix arrow navigation
  + Fix to-the-left selection bug (FireFox)
+ Do not duplicate auto-scroll code
+ Fix auto-scroll
+ Double-click bug
+ Frame height bug
+ createLineSelectionRange()
+ createWordSelectionRange()
+ Whole-word selection
+ Bug selecting first char in the line
+ Bug selecting word at the line start
+ Improve per-word selection mode: AA|#|#|AAA
+ Adding word to the selection
+ Implement selection range collisions
+ Fix selection of ###-word
+ Click on gutter select line
+ Bug with text trimming
+ Bug with selection
+ Bug with short lines
+ Triple-click - select line
+ Implement home - first non-space in line
+ Navigating with pgup/pgdn
+ updateScroll doesn't consider scrollbar height
+ Ensure autoscroll on double click
+ Do not duplicate code in handleMouseEvent
+ Remove addLine/WordSelectionRange()
+ SelectionPoint.belongsTo(range)
+ SelectionPoint.lessThan(point)
+ SelectionPoint.equalsTo(point)
+ SelectionPoint.greaterThan(point)
+ normalizeSelection()
+ Consider selection sollision
+ replaceText(range, text)
+ Delete selection on del/backspace/enter
+ Bug with originalTarget -> target
+ Selection merging on mouse drag
+ Separate function getLineLength(line)
+ Function getTextAt(line, [position[, +-length]])
+ Bug with removing 3 selections in one line

+ removeTextAt(range)
+ insertTextAt(range, text)
+ insertLineAt(range, text)

+ BOF, EOF, BOL, EOL, BOT

+ moveCursor(range, line, position, extend)
+ moveCursorTo(range, FireCode.BOF, extend)
+ moveCursorTo(range, FireCode.EOF, extend)
+ moveCursorTo(range, FireCode.BOL, extend)
+ moveCursorTo(range, FireCode.EOL, extend)
+ moveCursorTo(range, FireCode.BOT, extend)

+ moveCursorBy(range, FireCode.PAGE, +-1, extend)
+ moveCursorBy(range, FireCode.LINE, +-1, extend)
+ moveCursorBy(range, FireCode.WORD, +-1, extend)
+ moveCursorBy(range, FireCode.CHAR, +-1, extend)

+ Key delete
+ Key backspace
+ Key enter
  + Respect leading whitespace

+ Stopped passing ctrl + key
+ home/end do not work
+ Bug removing line with backspace
+ Errors printing in Opera
+ Cursor steps over the line

+ Ranges is getting reset on merge
+ Selection doesn't always merge
+ Selection clears sometimes
+ Optimize range calculations

+ ctrl + right/left doesn't jump between lines
+ drag doesn't work with single cursor
+ Per-word deleting delete/backspace

+ Enter tabs
+ Implement ctrl + A
+ implement ctrl + D

+ Fix cursor negative line positioning
+ Reuse cursor/highlight nodes + animate
+ Ctrl + home on empty line bug
+ Bug with cursor inside selection
+ Big selection rendering bug
+ Error with blank + backspace + type
+ Erroe with gutter - enumerates empty lines
+ Ctrl + arrows doesn't skip spaces
+ Implement ctrl + backspace
+ Fix whole-word selection
+ Simple gutter clic
+ Block indent/unindent
+ Implement smart indent
