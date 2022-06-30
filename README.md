# IPTV-org Stream Sniper

This is an automatic M3U file/provider that reads channels marked as online on the [iptv-org api](https://github.com/iptv-org/api). Simply add it as your IPTV Client's provider or you can download a copy by pasting the customizable link on your browser. By reading directly from the iptv-org api, it has the advantage of serving you high quality, fast loading streams with well structured channel information. Plus, this being from iptv-org, there is a higher guarantee of channel uptime.

---
### Configuration Parameters:
<dl>
  <dt>country</dt>
  <dd>2 letter country code separated by commas [ref. https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2]</dd>

>https://iptv-sniper.herokuapp.com/?country=us,uk​

##### default: us,uk,ca,au,nz

  <dt>quality</dt>
  <dd>desired resolution (represented in numbers) separated by commas</dd>

>https://iptv-sniper.herokuapp.com/?country=ph&quality=240,480,720​

##### default: all available resolutions

  <dt>nsfw</dt>
  <dd>include adult content</dd>

>https://iptv-sniper.herokuapp.com/index.php?country=us&quality=720,1080&nsfw=1​

##### default: hidden

  <dt>import</dt>
  <dd>include an external m3u file to add to the automatically generated playlist</dd>

>https://iptv-sniper.herokuapp.com/?country=ph&import=https://raw.githubusercontent.com/jmvbambico/iptv-sniper/master/channels_alpha.m3u
</dl>

---
### Developer Notes:
This is a work in progress. I have open-sourced this project to facilitate more efficient ways of doing the api scraping in case you know better.