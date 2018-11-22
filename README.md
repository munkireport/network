Network module
==============

Provides network interface information gathered by `networksetup -getinfo`

The table provides the following information per 'networkservice':

* service (string) Service name
* status (int) Active = 1, Inactive = 0
* ethernet (string) Ethernet address
* clientid (string) Client id
* ipv4conf (string) IPv4 configuration (automatic, manual)
* ipv4ip (string) IPv4 address
* ipv4mask (string) IPv4 network mask
* ipv4router (string) IPv4 router address
* ipv6conf (string) IPv6 configuration (automatic, manual)
* ipv6ip (string) IPv6 address
* ipv6prefixlen (int) IPv6 prefix length
* ipv6router (string) IPv6 router address

Remarks
---

* 'Wi-Fi ID' is stored as ethernet
* Entries without an ethernet address are not stored
* ipv4conf is undetermined when the ipv4 configuration is 'Off' in the user interface.

Configuration
-------------

VLANS

Plot VLANS by providing an array with labels and
a partial IP address of the routers. Specify multiple partials in array
if you want to group them together.
The router IP adress part is queried with SQL LIKE
Examples:
```
VLAN_LABELS='WIRED, WIFI, PRIVATE'

VLAN_RANGES_WIRED='211.88.10.1'
VLAN_NAME_WIRED='Wired Network'

VLAN_RANGES_WIFI='211.88.12.1, 211.88.13.1'
VLAN_NAME_WIFI='WiFi'

VLAN_RANGES_PRIVATE='10.%, 192.168.%'
VLAN_NAME_PRIVATE='Private Range'
```
