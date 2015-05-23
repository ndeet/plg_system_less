# System - Less Compiler
A Joomla! System Plugin, compiles templates .less files on page load only if they changed. Implemented with [lesscphp] and [lessphp] compilers to support Twitter Bootstrap (BS) versions 2.3 (Joomla! Core) but also BS version 3.2 and 3.3+.

[lesscphp]: https://github.com/leafo/lessphp
[lessphp]: https://github.com/oyejorge/less.php

## Compatibility
Joomla! 2.5.x up to 3.4.x
Twitter Bootstrap 2.x + 3.2 + 3.3
- for Bootstrap 2.3 (shipped with Joomla! Core) use lessc-0.3.9 compiler
- for Bootstrap 3.3+ please use the lessphp-1.7.x.x compiler
- for Bootstrap 3.2 please use either lessc-0.4.0 or the new lessphp-1.7.x.x compiler

### Changelog
See [CHANGELOG.md](CHANGELOG.md)

## Features Overview
+ compiles template.less file and all imported files to template.css
+ supports Twitter Bootstrap 2.x and 3.2 (thanks to @robwent)
+ supports Twitter Bootstrap 3.3+ with less.php compiler [lessphp] (choose lessphp-1.7.x.x from compiler dropdown)
+ uses caching to track changes and better performance
+ option to force compilation on each reload
+ option to compress .css output
+ option to preserve comments
+ less and css path configurable
+ client-side compiler for easier .less debugging, thanks to @piotr-cz
+ compatible with J! 2.5 + 3.3 + 3.4
+ Option to parse only frontend, backend or both
+ Paths for frontend and backend template configurable
+ fr-FR translation contributed by lomart.fr
+ ru-RU translation contributed by Pazys

Client side compiler by @piotr-cz for easy debugging. It maps the .css file back to .less files. This means you can see where classes have been defined. Speeds up template development.
See [clientside compiler instructions](https://github.com/ndeet/plg_system_less/wiki/Clientside-Compiler-instructions)

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
