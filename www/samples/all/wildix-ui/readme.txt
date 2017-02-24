Author: Sergey Morkovkin
Email: sergeymorkovkin@gmail.com
Mob.: +38 050 445 01 45
Skype: sergeymorkovkin
WWW: http://morkovkin.info


Requirements:
    
    Implement a refactored user interface for WMP, WMS, CTI Connect (desktop & mobile).
    The resulting interface should enhance usability and unify system look. Designed
    UI should provide common framework, components and graphics. All three interfaces
    should have single sign-on and user should easily switch between UIs.
    
    Interface should include the following features:
    
    - Wildix Management System
      - Users List
      - Groups List
      - Groups ACL
      - Dialplans List
      - Dialplan Rules Editor
      
    - CTI Connect
      - Contacts List
      - Events List: Chats, Calls, Faxes, Post-it's
      - Chat / Conference
      
    - [POSTPONED] Wildix Management Portal
      - PBXs List + Filtering
      - Users List + Filtering
      
    - [POSTPONED] CTI Connect Mobile
      - Chats List
      - Chat Window
      
      
Time tracking:
  - [04.10.2011] Exploring the system, collaborating with Artem, finding suitable designs.
  - [05.10.2011] Exploring the system, preparing the document, developing the concept.
  - [06.10.2011] Finished exploring the system, developing the concept details, login screens.
  - [07.10.2011] Developing CTI Connect basic layout. Thinking on usability, optimizing things.
  - [10.10.2011] 
    
    + Try to swap search + button.
    + Normalize header gradients.
    + Enhance user info.
    + Swap user info and logo.
    + Normalize menu item widths.
    + Add menu items: chat, tasks.
    + Add context help icon.
    + Add data to contacts grid.
    + Try adding contact type icons.
    + Batch operations control.
    + Pagination control.
    + Dialpad to the left.
    
  - [11.10.2011]
    
    + Dialpad enhancements
      + Normalize header height.
      + Clear number button.
      + Enhance dialpad button sizing.
      + Call button.
      + Enhance clear button.
      + Enhance rounded corners radius.
      + Calling state for a dialpad.
      + Call state or time.
      + To contact: avatar, info.
      + To unknown number.
      + End call button.
      + Enhance contacts display.
      
    + Contact editing form.
      + Adding phone numbers, email, address & etc.
      + Star for adding to favorites, 2 states.
      + Actions: call, email, sms, fax, chat.
    
  - [12.10.2011]
    
    + User event history.
      + Filter by date.
      + Filter by type: in-call, out-call, missed-call.
      + Call types: finished, received, missed.
      + Table data.
      
  - [13.10.2011]
    
    + Chat/conference.
      + Chat room tabs at the top of chat window.
      + Button for closing chat room.
      + Enhance chat contacts list.
      + Display user status in the list.
      + Chat and invite buttons for the contact.
      + Enhance contacts, don't use bold.
      + Recent chat list.
      + Messages in a chat window.
      
    + Users List
    + Groups List
    + Dialplans List
    
  - [14.10.2011]
    
    + Groups ACL
    + Dialplan Rules Editor
    
    
  - [27.10.2011]

    + Rename link 'settings' to 'profile' in user info section.
    + profile | logout - remove capital letters (everywhere).
    + Switch links between CTI and WMS. CTI Connect and WMS will be merged into a single UI. So, no switching links are required.
    + Enhance dialpad styling in a dialing mode.
    + Indicate that phone number field is editable.
    + Try adding tags colored background.
    + Add sorting elements to the grid.
    + Tags selector + tags in a grid: synchronize coloring. Different colors look bad on navy bg. Single color is a good choice.
    + Separate command buttons for the list.
    
  - [28.10.2011]

    + Online status control. Do users really need this?!
    + Move contact button somewhere. Make it more usable.
    + Rename History tab to Dashboard, move to 1st place.
    + Alternate design of contact editing form (less space).
    + Unify pager styles (list pager and form pager).
    
    + Explain dialer functionality.
      
      Dialer panel is made to dial phone number using web interface and mouse alone.
      Any phone number can be pasted from clipboard and dialed with a click. Another
      important feature is displaying calling state information (who am I talking with)
      and call management (kick user off the conference call). 
      
    + Alternate list view with thumbnails.
      
      Don't recommend to do this for the following reasons:
      
      - Every list page contains 15-20 items meaning 15-20 requests to PBX. Kills the performance.
      - List's intention is to present as many items as possible in a tiny space without scrolling.
      - Thumbnails will require user to scroll to reach list controls or pager. No convenience.
      - Most contacts will have no thumbnails. So, we loose convenience with no reason or outcome.
      
      Instead I've implemented a list popup for showing a brief contact information. This includes thumbnail.
      Traffic isn't increased and list still looks intelligent. Contact popup adds control buttons for quick
      operations.
      
    + Contact popup actions: add to favorites, start chat, send email, send SMS, send fax, call.
    
  - [29.10.2011]
    
    + Consolidating dashboard view for events.
    + Chat window scrollbar to the right.
    
  - [30.10.2011]
    
    + Replace menu to a new version.
    + Enhance form buttons (remove color border).
    + Refactor dialplan/groups editors.
    
    
Postponed tasks:
    
    - Further versions:
      
      - Mac-style unified search (Dimitry).
      - Add more pages for settings (Dimitry).
      - Add a calendar tab (future version).
      - Chat without changing tab. Suggest avoid popups.
      - Consolidated view for chat, messages, faxes, calls.
      - Contacts window with presence and geolocation.
      - Forsee ability to move to an iPhone.
      - Print/export icons for list/contact.
      - Printing layout for both list and form.
      - Task/comment adding subform for contact form.
    
    - Base UI elements:
      - Dropdown menu.
      - Delete confirmation is a tooltip for a delete button.
      - Empty table style.
      - Notification popup alerts for both lists/forms.
      - Popup context dialpad for entering phone numbers.
      - Context help sample page.
      - Color balancing screen.
      - Popup windows screen.

      
    
Question tracking:
    
    + What is a global structure of the system?
      
      This project includes a refactored web-based UI for Wildix PBX devices.
      There are admin panels for 3 areas:
      
      - Wildix Management Portal (WMP) - UI for manufacturer and customers. 
        Wildix employees use WMP to monitor unit sales, customers and provide technical support.
        Wildix customers use WMP to manage unit licenses and ask for technical assistance.
        
      - Wildix Management System (WMS) - UI for managing single hardware unit.
        Provides an access to Wildix customers for unit configuring. This includes
        users, groups, access policies, dialplans and other settings.
        
      - CTI Connect (CTI) - UI for end-users of a unit.
        This interface is mainly used by customer company employees for interaction
        and contact information management. There is also a mobile version of CTI.
      
      WMP is hosted on Wildix web servers and is available on the Internet.
      WMS and CTI are hosted on a PBX units. Those interfaces are mainly used by
      customer employees.
      
    + Wildix UI access:
      
      - WMP: http://pbx.wildix.com/portal; sergey.morkovkin; wildix123456;
      - WMS: http://46.151.192.230:8082; admin; a0vpIM;
      - CTI: http://46.151.192.230:8082/cticonnect; admin; a0vpIM;
      
    + PBX architecture:
      
      - Creates a browser-hosted client-side database to lower the load on PBX.
      - CTI Connect doesn't support FF 7, better to use Chrome.
      - PBX stops responding on HTTP while importing an address book.
      - Faxes are always in TIFF and can only be shown by browsers that support it.
      - PBX: 1Ghz single core, 1Gb drive, 500 Mb taken by OS & software.
      - BPX always uses an external database which is one of: MySQL, MS SQL ot Sqlite.
      - In CTI Connect web browser is constantly showing 'loading state' of the page.
      
    + What should be implemented for a Dialplan?
    + What should be implemented for mobile version?
    + Does customer expect HTML slicing / JS programming?
    + How does customer see single sign-on for WMP / WMS?
    + How many kernels does PBX processor have? 1 kernel.
    + Where do I get Wildix logotypes in vector. 
    + How does dialplan rules function? Examples.
      - Trunks: SIP, ASDN > BRI - 2 / PRI - 30, GSM, FXO - 1 line only, 
      - Actions: call control, services, conditions, contacts, IVR/spunds, set, other.
    + What is meant by 'user management' in WMP? Only a list? Or a full CRUD? Suggest to focus on CTI Connect.
    + Is it okay to only draw desktop version of chat/conference and bypass mobile? Suggest to focus on desktop.
    
    + Layout design and coloring.
        
        Fluid layouts are highly unrecommended for administrative purposes since breaks
        visual acceptance of an interface. It is especially uncomfortable for wide-screen
        users. Optimal choice is to use fixed layout in combination with a positioning
        toggle switch (centered or 15% left from center).
        
        - Fixed centered layout 960px wide or less (will check later).
        - Toggle switch to move layout 15% left from center + 2 state icons.
        - Page background is dark, but not black to keep intelligent look.
        - Header background color matches panel type to enhance visual perceptance.
        - Suggested colors are: WMP - royal red, WMS - ice blue, CTI Connect - Earth Green.
        - Working area is bright but not white to contrast with input fields.
        
        
    + Header concept.
        
        - Header should contain the following: [AVATAR] - [USER INFO] - [MENU] - [WILDIX LOGO].
          
          This exact sequence of elements serves a few purposes: make compact low-height header, 
          respect user attention by presenting only the most important information in an expected
          order and provide a quick eye-catcher (avatar). Yet Wildix logo is presented in an
          calm respectable way.
        
        - Avatar is clickable and contains a link beneath itself: 'upload' or 'change'.
        - Hovering an avatar picture changes the style of this link to 'underline'.
        - User info contains full name followed by a telephone number preceeded by '#'.
        - Two links are placed under user name: 'profile settings' and 'logout'.
        - Beneath is a status switch which is only used by CTI Connect, custom dropdown.
        - Status switch element contains icons, text and dropdown arrow to the right.
        - By default status switch doesn't have border. Border only appears on hover.
        
        
    + Menu concept.
        
        Menu should be at least 70px in height, provide a notification number icons, remain
        possibility for a dropdown and include selected item pointer.
        
        Main menu items should include:
        - WMP: Users + Groups, PBXs
        - WMS: Users + Groups, Phonebook, Settings
        
    + Login concept.
        
        - Every PBX device has it's own login screen serving both CTI Connect and WMS. Layout color matches CTI Connect (green earth).
        - WMP has a separate login screen and it's layout color matches WMP coloring (royal red).
        - Both login screens have a link to switch between each other. WMP remembers referrer url in cookie to provide PBX return link.
        - Both login screens have password recovery and registration links.
    
    + UI separation concept.
        
        One of customer requirements is to implement an easy switching between user interfaces: WMP, WMS and CTI Connect.
        My suggestion is to simplify the concept for the user point of view.
        
        - Make WMS an integral part of CTI Connect. WMP main functional item should be placed under settings in the main
          menu. Other screens should be affected at minimum possible. Administrator pannel header is royal red for easy
          intuitive distinction.
        
        - Add a link to WMP in a BPX management panel.
        
        - In WMP's PBX list add a link to certain BPX management panel. Not sure if it's technically possible since some
          PBX might be behind firewall (while WMP is always available on the Internet).
        
        NOTE: I don't recommend to integrate one interface into another (header from PBX and content from WMP, for example).
        
    + History concept.
      
      In present implementation events and messages are combined into one table. However, those are different entities. Events
      stands for something that happens (received voicemail), while voicemail message contains the message itself. Same for faxes,
      sms, emails and chat sessions. Event history is only a journal for registering user communication activity.
