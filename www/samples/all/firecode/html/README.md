FireCode
========

FireCode is a lightweight web-based programming code editor with the following features:

    - Has no other dependencied except ECMA JavaScript
    - Has clean, object-oriented code, well-thought API
    - Entire codebase fits into 5.6Kb minified and gzipped
    - Works in all major browsers: FF, Chrome, Opera, IE
    - Optimized for speed and can load very large files
    - Contains text selection framework made from scratch
    - Supports multiple cursors (test with ctrl + click)
    - Block indent/unindent with Select + Tab / Shift + Tab
    - Press Ctrl + D to duplicate line
    - Press Ctrl + A to select entire text
    
IF YOU NEED HIGH-QUALITY UI COMPONENT DEVELOPMENT, I'M ALWAYS AT YOUR SERVICE WILLING TO HELP.

In order to create multiple cursors, I've had to refuse from using design mode of an IFRAME.
Design mode supports only one cursor, has different selection API across browsers and makes
it impossible to fully control selection/cursor appearance (e.g. line height, cursor width,
blinking interval, cursor opacity animation and so on). The only suitable solution to this
problem was creating text editor from scratch by using standard DOM elements and JavaScript.
FireCode's cursors, selection ranges, line highlighs, gutter and text are represented with
P and DIV elements. 

FireCode is rendering key elements in separate layers layed one on another: line highlights,
text selection ranges, lines of text, cursors and gutter. The key secret here is that due to
the accuracy of code all those layers are synced together creaing an illusion of consistency.

When it comes to performance, FireCode is highly optimized. It is using the least number of
DOM elements at every moment of it's work. For example, every selection range takes at least
1 and no more than 3 DIV elements. When selection is changed, DIV elements from previous range
are reused when possible preventing browser from memory operations.

Special attention has been paid to scrolling. When you scroll, FireCode only renders visible
lines of code and removes those that were scrolled outside of the view frame. The smoothness
of the movement is the result of an algorythm accurately managing top-margin offset and lines
adding/removing.

Besides all of that, FireCode has full-blown selection range API built from scratch. This API
supports multiple cursors, has algorythm preventing selection range collisions and works great
with keyboard and mouse. For example, when you double-click the word closer to the right word
boundary, it's placing the cursor at the right word boundary. On the other hand, double-click
the word closer to the left boundary is placing the cursor to the left. Per-word navigation is
also implemented in FireCode. Try navigating through words with Ctrl + Right/Left.

The following features are coming: cut/copy/paste, undo/redo, syntax parser and highlight. Let
me know what you think, kindly share your ideas and proposals.

IF YOU NEED HIGH-QUALITY UI COMPONENT DEVELOPMENT, I'M ALWAYS AT YOUR SERVICE WILLING TO HELP.

--

Regards,

Sergey Morkovkin

Email: sergeymorkovkin@gmail.com
Skype: sergeymorkovkin.fl
Phone: +38 (050) 445-01-45
Website: http://morkovkin.info