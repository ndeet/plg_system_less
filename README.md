# System - Less Compiler
A Joomla! System Plugin, compiles templates .less files on page load only if they changed. Implemented with [lessphp]

Client side compiler by @piotr-cz for easy debugging. It maps the .css file back to .less files. This means you can see where classes have been defined. Speeds up template development.
See [clientside compiler instructions](https://github.com/ndeet/plg_system_less/wiki/Clientside-Compiler-instructions)

[lessphp]: https://github.com/leafo/lessphp

## Compatibility
Joomla! 2.5.x up to 3.3.x
Twitter Bootstrap 2.x + 3.x

### Changelog
See [CHANGELOG.md](CHANGELOG.md)

## Features Overview
+ compiles template.less file and all imported files to template.css
+ supports Twitter Bootstrap 2.x and 3.x (thanks to @robwent)
+ uses caching to track changes and better performance
+ option to force compilation on each reload
+ option to compress .css output
+ option to preserve comments
+ less and css path configurable
+ client-side compiler for easier .less debugging, thanks to @piotr-cz
+ compatible with J! 2.5 + 3.x
+ Option to parse only frontend, backend or both
+ Paths for frontend and backend template configurable
+ fr-FR translation contributed by lomart.fr
+ ru-RU translation contributed by Pazys

## Installation
a) Download latest release from Tags and install via Joomla! Extension Manager
https://github.com/ndeet/plg_system_less/tags

b) Download latest zipball (development state) and install via Joomla! Extension Manager
https://github.com/ndeet/plg_system_less/zipball/master

## Feedback and Issues
https://github.com/ndeet/plg_system_less/issues

## Help and Contribute
a) By pull requests

b) For small fixes/translations you can also use Github's built-in editor
http://docs.joomla.org/Github_using_the_web

## Joomla Extensions Directory
http://extensions.joomla.org/extensions/miscellaneous/development/22424
