# IPTV-org Stream Sniper

This is an automatic M3U file/provider that reads channels marked as online on the [iptv-org api](https://github.com/iptv-org/api). Simply add it as your IPTV Client's provider or you can download a copy by pasting the customizable link on your browser. By reading directly from the iptv-org api, it has the advantage of serving you high quality fast loading streams, and the channel information is well structured. Plus, this being from iptv-org, there is a higher guarantee of channel uptime.

### Configuration Parameters:
`country`:  
2 letter country code separated by commas [ref. <https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2>]

**link example**: <https://iptv-sniper.herokuapp.com/?country=us,uk​>
>defaults to us,uk,ca,au,hk,sg,ph if unprovided​
`quality`:  
desired resolution (represented in numbers) separated by commas

**link example**: <https://iptv-sniper.herokuapp.com/?country=ph&quality=240,480,720​>
>defaults to not shown if unprovided​
`nsfw`:  
include adult content

**link example**: <https://iptv-sniper.herokuapp.com/index.php?country=us&quality=720,1080&nsfw=1​>  
>defaults to not shown if unprovided​

### Developer Notes:
This is a work in progress. I have open-sourced this project to facilitate more efficient ways of doing the api scraping in case you know better.