# howiusescss

How I currently use SCSS with only a reliance on [scssphp](https://github.com/leafo/scssphp).

Usage: /scsser/site.css === compilation of /assets/site.scss

This does use temporary files so make sure they are being cleaned!

N.B. you may hate all of this but it is more of a reference for myself than anything.

### Style

I always write my CSS on a single line to dramatically reduce vertical scrolling within large CSS and SCSS files. I also note that many programs cope better with a bit of horizontal scroll compared to an awful lot of vertical scroll.

I alphabetise the properties to make them easier to locate quickly and also to correspond to the ordering within Firebugs CSS display.

All lines are tab indented and I don't separate the properties with spaces, probably from anciently making CSS as small as possible.

I avoid unnecessary and unused parts of the selectors keeping the output as lean as possible.

I don't globally set `box-sizing:border-box;` because I have a fondness of `content-box` but do use it within individual elements that need it.