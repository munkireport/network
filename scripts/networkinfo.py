#!/usr/local/munkireport/munkireport-python3

import os
import subprocess
import sys
import plistlib
import re
import platform

from Foundation import CFPreferencesCopyAppValue

sys.path.insert(0, '/usr/local/munkireport')
from munkilib.purl import Purl
from Foundation import NSHTTPURLResponse

def get_network_info():
    '''Uses system profiler to get info about the network'''
    output = bashCommand(['/usr/sbin/system_profiler', 'SPNetworkDataType', '-xml'])

    try:
        try:
            plist = plistlib.readPlistFromString(output)
        except AttributeError as e:
            plist = plistlib.loads(output)
        # system_profiler xml is an array
        sp_dict = plist[0]
        items = sp_dict['_items']
    except Exception:
        return {}

    # Get network locations
    network_locations = get_network_locations()

    # Get external IP only once
    external_ip = get_external_ip()

    # Make sure Airport is only scanned once
    # Alternatively, set this to 1 to disable Airport scanning and create a new client package
    # Useful if you keep getting timeouts on this script
    airport_scanned = 0

    out = []
    for obj in items:
        device = {'status':0}
        for item in obj:
            if item == '_name':
                device['service'] = obj[item]
                if device['service'] == "Wi-Fi" or device['service'] == "AirPort":

                    try:
                        # If less than macOS 13 (Darwin 22), use legacy method to get wifi data
                        if getDarwinVersion() < 22:
                            output = bashCommand(['/usr/libexec/airportd', 'info']).decode("utf-8", errors="ignore")
                        else:
                            output = bashCommand(['/usr/bin/wdutil', 'info']).decode("utf-8", errors="ignore")

                        for line in output.split('\n'):
                            if "Active PHY: " in line:
                                device['activemedia'] = line.replace("Active PHY: ","").strip()
                                break
                            elif "    PHY Mode             : " in line:
                                device['activemedia'] = "802."+(line.replace("    PHY Mode             : ","").strip())
                                break
                    except Exception:
                        pass

            elif item == 'ip_address':
                device['status'] = 1
            elif item == 'interface':
                device['bsd_interface'] = obj[item]
            elif item == 'spnetwork_service_order':
                device['order'] = obj[item]
            elif item == 'hardware' and obj[item] == "AirPort" and airport_scanned == 0:
                device = merge_two_dicts(device, get_airport_info())
                airport_scanned = 1
            elif item == 'DNS':
                if "SearchDomains" in obj[item]:
                    device['searchdomain'] = ', '.join(obj[item]["SearchDomains"])
                if "ServerAddresses" in obj[item]:
                    device['ipv4dns'] = ', '.join(obj[item]["ServerAddresses"])
            elif item == 'Ethernet':
                if "MAC Address" in obj[item]:
                    device['ethernet'] = obj[item]["MAC Address"].upper()
                if "MediaSubType" in obj[item]:
                    device['activemedia'] = obj[item]["MediaSubType"]
                if "MediaSubType" in obj[item] and "MediaOptions" in obj[item] and len(obj[item]["MediaOptions"]) != 0:
                    device['activemedia'] = obj[item]["MediaSubType"]+" ("+' '.join(obj[item]["MediaOptions"])+")"
            elif item == 'IPv4':
                if "ARPResolvedHardwareAddress" in obj[item]:
                    device['ipv4switchmacaddress'] = obj[item]["ARPResolvedHardwareAddress"].upper()
                if "Addresses" in obj[item]:
                    device['ipv4ip'] = ', '.join(obj[item]["Addresses"])
                if "ConfigMethod" in obj[item]:
                    device['ipv4conf'] = obj[item]["ConfigMethod"]
                if "DestAddresses" in obj[item]:
                    device['ipv4destaddresses'] = ', '.join(obj[item]["DestAddresses"])
                if "DHCPClientID" in obj[item]:
                    device['clientid'] = obj[item]["DHCPClientID"]
                if "NetworkSignature" in obj[item] and "VPN.RemoteAddress=" in obj[item]["NetworkSignature"]:
                    device['vpnservername'] = re.sub('VPN.RemoteAddress=','',obj[item]["NetworkSignature"])
                if "Router" in obj[item]:
                    device['ipv4router'] = obj[item]["Router"]
                if "OverridePrimary" in obj[item]:
                    device['overrideprimary'] = obj[item]["OverridePrimary"]
                if "ServerAddress" in obj[item]:
                    device['vpnserveraddress'] = obj[item]["ServerAddress"]
                if "SubnetMasks" in obj[item]:
                    device['ipv4mask'] = ', '.join(obj[item]["SubnetMasks"])
            elif item == 'IPv6':
                if "ARPResolvedHardwareAddress" in obj[item]:
                    device['ipv6switchmacaddress'] = obj[item]["ARPResolvedHardwareAddress"].upper()
                if "Addresses" in obj[item]:
                    device['ipv6ip'] = ', '.join(obj[item]["Addresses"])
                if "ConfigMethod" in obj[item]:
                    device['ipv6conf'] = obj[item]["ConfigMethod"]
                if "DestAddresses" in obj[item]:
                    device['ipv6destaddresses'] = ', '.join(obj[item]["DestAddresses"])
                if "DHCPClientID" in obj[item]:
                    device['ipv6clientid'] = obj[item]["DHCPClientID"]
                if "NetworkSignature" in obj[item] and "VPN.RemoteAddress=" in obj[item]["NetworkSignature"]:
                    device['ipv6vpnservername'] = re.sub('VPN.RemoteAddress=','',obj[item]["NetworkSignature"])
                if "OverridePrimary" in obj[item]:
                    device['ipv6coverrideprimary'] = obj[item]["OverridePrimary"]
                if "Router" in obj[item]:
                    device['ipv6router'] = obj[item]["Router"]
                if "ServerAddress" in obj[item]:
                    device['ipv6vpnserveraddress'] = obj[item]["ServerAddress"]
                if "SubnetMasks" in obj[item]:
                    device['ipv6mask'] = ', '.join(obj[item]["SubnetMasks"])
            elif item == 'dhcp':
                if "dhcp_domain_name" in obj[item]:
                    device['dhcp_domain_name'] = obj[item]["dhcp_domain_name"]
                if "dhcp_domain_name_servers" in obj[item]:
                    device['dhcp_domain_name_servers'] = re.sub(',',', ',obj[item]["dhcp_domain_name_servers"])
                if "dhcp_lease_duration" in obj[item] and obj[item]["dhcp_lease_duration"] != 0:
                    device['dhcp_lease_duration'] = obj[item]["dhcp_lease_duration"]
                if "dhcp_routers" in obj[item]:
                    device['dhcp_routers'] = obj[item]["dhcp_routers"]
                if "dhcp_server_identifier" in obj[item]:
                    device['dhcp_server_identifier'] = obj[item]["dhcp_server_identifier"]
                if "dhcp_subnet_mask" in obj[item]:
                    device['dhcp_subnet_mask'] = obj[item]["dhcp_subnet_mask"]

        # Add in additional network info and external IP address if active
        if device['status'] == 1:
            device = merge_two_dicts(device, get_additional_info(device['bsd_interface']))
            device['externalip'] = external_ip

            # Add in location information to network device only if active
            for location in network_locations:
                if location["spnetworklocation_isActive"] == "yes":
                    for service_location in location["spnetworklocation_services"]:
                        if 'bsd_device_name' in service_location and 'bsd_interface' in device:
                            if "SMB" in service_location:
                                if "NetBIOSName" in service_location["SMB"]:
                                    device['netbiosname'] = service_location["SMB"]["NetBIOSName"]
                                if "Workgroup" in service_location["SMB"]:
                                    device['workgroup'] = service_location["SMB"]["Workgroup"]
                            device['location'] = location["_name"]
                            break
                    break

        out.append(device)

        # Run ifconfig so we only have to run it once
        ifconfig_data = bashCommand(['/sbin/ifconfig']).decode("utf-8", errors="ignore").split('\n')

    # Check for and add bond, tuns, and vmnets
    out = out + get_bond_info(ifconfig_data) + get_tunnel_info(ifconfig_data) + get_vmnet_info(ifconfig_data)

    return out

def get_network_locations():
    '''Uses system profiler to get info about the network locations'''
    output = bashCommand(['/usr/sbin/system_profiler', 'SPNetworkLocationDataType', '-xml'])

    try:
        try:
            plist = plistlib.readPlistFromString(output)
        except AttributeError as e:
            plist = plistlib.loads(output)
        # system_profiler xml is an array
        sp_dict = plist[0]
        return sp_dict['_items']
    except Exception:
        return {}

def get_additional_info(interface):
    network = {}
    mtudata = bashCommand(['/usr/sbin/networksetup', '-getMTU', interface]).decode("utf-8", errors="ignore")
    if "Current Setting" in mtudata and "Error: The parameters were not valid" not in mtudata:
        network["activemtu"] = re.sub('[^0-9]','', re.sub(r"[\(\[].*?[\)\]]", "", mtudata))
    else:
        return network

    current_media = re.sub('Current: ','',''.join(bashCommand(['/usr/sbin/networksetup', '-getmedia', interface]).decode("utf-8", errors="ignore").split('\n')[:1])).strip()
    if current_media != "" and "Could not find hardware port or device named" not in current_media:
        network["currentmedia"] = current_media

    vlan_data = re.sub("There are no VLANs currently configured on this system.","",re.sub('\n',', ',bashCommand(['/usr/sbin/networksetup', '-listVLANs']).decode("utf-8", errors="ignore")))[:-2]
    if vlan_data != "":
        network["vlans"] = vlan_data

    validmtudata = bashCommand(['/usr/sbin/networksetup', '-listvalidMTUrange', interface]).decode("utf-8", errors="ignore")
    if "Valid MTU Range:" in validmtudata and "Error: The parameters were not valid" not in validmtudata:
        network["validmturange"] = re.sub('Valid MTU Range: ','', validmtudata)

    return network

def get_external_ip():
    ip_address_server = str(get_pref_value('IpAddressServer', 'MunkiReport'))

    if len(ip_address_server) == 0:
        ip_address_server = "https://api.ipify.org"

    if "127.0.0.1" in ip_address_server:
        return ""
    else:
        try:
            return curl(ip_address_server).decode("utf-8", errors="ignore")
        except Exception:
            return ""

def get_bond_info(ifconfig_data):
    # Bond, James Bond
    try:
        bond_adapters = ifconfig_data
        bonds = []

        for bond_adapter in bond_adapters:
            if "bond" in bond_adapter and ": flags=" in bond_adapter:
                adapter = bond_adapter.split(': flags=')[0].strip()
                bond = {'status':0}
                bond_ip = bashCommand(['/sbin/ifconfig', adapter]).decode("utf-8", errors="ignore")
                bond_lines = bond_ip.split('\n')

                for bond_line in bond_lines:
                    if "inet" in bond_line and "inet6" not in bond_line:
                        bond['ipv4ip'] = ''.join(re.sub('inet ','',bond_line.strip()).split(' ')[0]).strip()
                        bond['service'] = adapter
                        bond['status'] = 1
                    elif "inet6" in bond_line and "fe80::" not in bond_line:
                        bond['ipv6ip'] = ''.join(re.sub('inet6 ','',bond_line.strip()).split(' ')[0]).strip()
                        bond['service'] = adapter
                        bond['status'] = 1
                    elif "ether" in bond_line:
                        bond['ethernet'] = re.sub('ether ','',bond_line.strip()).split(' ')[0].strip().upper()
                    elif "mtu" in bond_line:
                        bond["activemtu"] = re.sub('[^0-9]','', bond_line.split(' mtu ')[-1])
                    elif "media: " in bond_line:
                        bond['activemedia'] = re.sub(r'\)','', re.sub(r'\(','left_para', bond_line).split('left_para')[1]) # tbase
                        bond['currentmedia'] = re.sub('media:','', bond_line.strip().split(' ')[1]) # autoselect
                bonds.append(bond)
        return [_f for _f in bonds if _f]

    except:
        return []

def get_tunnel_info(ifconfig_data):
    try:
        utun_adapters = ifconfig_data
        utuns = []

        for utun_adapter in utun_adapters:
            if "utun" in utun_adapter:
                adapter = utun_adapter.split(': flags=')[0].strip()

                utun = {}
                utun_ip = bashCommand(['/sbin/ifconfig', adapter]).decode("utf-8", errors="ignore")
                utun_lines = utun_ip.split('\n')

                for utun_line in utun_lines:
                    if "inet" in utun_line and "inet6" not in utun_line:
                        utun['ipv4ip'] = ''.join(re.sub('inet ','',utun_line.strip()).split(' ')[0]).strip()
                        utun['service'] = adapter
                        # utun['status'] = 1
                    elif "inet6" in utun_line and "fe80::" not in utun_line:
                        utun['ipv6ip'] = ''.join(re.sub('inet6 ','',utun_line.strip()).split(' ')[0]).strip()
                        utun['service'] = adapter
                        # utun['status'] = 1
                    elif "ether" in utun_line:
                        utun['ethernet'] = re.sub('ether ','',utun_line.strip()).split(' ')[0].strip().upper()

                utuns.append(utun)

        return [_f for _f in utuns if _f]

    except:
        return []

def get_vmnet_info(ifconfig_data):
    try:
        vmnet_adapters = ifconfig_data
        vmnets = []

        for vmnet_adapter in vmnet_adapters:
            if ("vmnet" in vmnet_adapter or "vmenet" in vmnet_adapter) and "member: " not in vmnet_adapter:
                adapter = vmnet_adapter.split(': flags=')[0].strip()

                vmnet = {'service':adapter}
                vmnet_ip = bashCommand(['/sbin/ifconfig', adapter]).decode("utf-8", errors="ignore")
                vmnet_lines = vmnet_ip.split('\n')

                for vmnet_line in vmnet_lines:
                    if "inet" in vmnet_line and "inet6" not in vmnet_line:
                        vmnet['ipv4ip'] = ''.join(re.sub('inet ','',vmnet_line.strip()).split(' ')[0]).strip()
                    elif "inet6" in vmnet_line and "fe80::" not in vmnet_line:
                        vmnet['ipv6ip'] = ''.join(re.sub('inet6 ','',vmnet_line.strip()).split(' ')[0]).strip()
                    elif "ether" in vmnet_line:
                        vmnet['ethernet'] = re.sub('ether ','',vmnet_line.strip()).split(' ')[0].strip().upper()

                vmnets.append(vmnet)
        return vmnets

    except:
        return []

def get_airport_info():

    output =  bashCommand(['/usr/sbin/system_profiler', 'SPAirPortDataType', '-xml', '-timeout', '8.4'])

    try:
        try:
            plist = plistlib.readPlistFromString(output)
        except AttributeError as e:
            plist = plistlib.loads(output)

        # system_profiler xml is an array
        sp_dict = plist[0]
        obj = sp_dict['_items'][0]['spairport_airport_interfaces'][0]
    except Exception:
        return {}

    device = {'airdrop_supported':0,'wow_supported':0}
    for item in obj:
        if item == 'spairport_airdrop_channel':
            device['airdrop_channel'] = obj[item]
        elif item == 'spairport_caps_airdrop' and obj[item] == "spairport_caps_supported":
            device['airdrop_supported'] = 1
        elif item == 'spairport_caps_wow' and obj[item] == "spairport_caps_supported":
            device['wow_supported'] = 1
        elif item == 'spairport_supported_channels':
            device['supported_channels'] = ', '.join(str(e) for e in obj[item])
        elif item == 'spairport_supported_phymodes':
            device['supported_phymodes'] = obj[item]
        elif item == 'spairport_wireless_card_type':
            device['wireless_card_type'] = re.sub('spairport_wireless_card_type_airport_extreme ','AirPort Extreme ',obj[item])
        elif item == 'spairport_wireless_country_code':
            device['country_code'] = obj[item]
        elif item == 'spairport_wireless_firmware_version':
            device['firmware_version'] = obj[item]
        elif item == 'spairport_wireless_locale':
            device['wireless_locale'] = obj[item]
    return device

def getDarwinVersion():
    """Returns the Darwin version."""
    darwin_version_tuple = platform.release().split('.')
    return int(darwin_version_tuple[0])

def get_pref_value(key, domain):

    value = CFPreferencesCopyAppValue(key, domain)

    if(value is not None):
        return value
    elif(value is not None and len(value) == 0 ):
        return ""
    else:
        return ""

def bashCommand(script):
    proc = subprocess.Popen(script, shell=False, bufsize=-1,
                            stdin=subprocess.PIPE,
                            stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    (output, unused_error) = proc.communicate()
    return output

def hide_curl_log(msg, *args):
    # Empty function to hide curl log output
    pass

def curl(url):
    # Curl function lovely copied from reportcommon.py 

    options = dict()
    options["url"] = url
    options["logging_function"] = hide_curl_log # Local function to suppress messages
    options["connection_timeout"] = 5 # Set connection timeout
    options["follow_redirects"] = False # Set follow_redirects

    # Build Purl with initial settings
    connection = Purl.alloc().initWithOptions_(options)
    connection.start()
    try:
        while True:
            # if we did `while not connection.isDone()` we'd miss printing
            # messages if we exit the loop first
            if connection.isDone():
                break

    except (KeyboardInterrupt, SystemExit):
        # safely kill the connection then re-raise
        connection.cancel()
        raise
    except Exception as err:  # too general, I know
        # Let us out! ... Safely! Unexpectedly quit dialogs are annoying...
        connection.cancel()
        # Re-raise the error as a GurlError
        print("Error: -1 "+connection.error.localizedDescription())
        return ""

    if connection.error != None:
        # Gurl returned an error
        if connection.SSLerror:
            print("SSL error detail: %s", str(connection.SSLerror))
        print("Error: "+ str(connection.error.code()), connection.error.localizedDescription())
        return ""

    if connection.response != None and connection.status != 200:
        print("Status: %s", connection.status)
        print("Headers: %s", connection.headers)
    if connection.redirection != []:
        print("Redirection: %s", connection.redirection)

    connection.headers["http_result_code"] = str(connection.status)
    description = NSHTTPURLResponse.localizedStringForStatusCode_(connection.status)
    connection.headers["http_result_description"] = description

    if str(connection.status).startswith("2"):
        return connection.get_response_data()
    else:
        # there was an HTTP error of some sort.
        print(connection.status, "%s failed, HTTP returncode %s (%s)"
            % (url, connection.status, connection.headers.get("http_result_description", "Failed"),),
        )
        return ""

def merge_two_dicts(x, y):
    z = x.copy()
    z.update(y)
    return z

def main():
    '''Main'''

    # Remove old networkinfo.sh  script, if it exists
    if os.path.isfile(os.path.dirname(os.path.realpath(__file__))+'/networkinfo.sh'):
        os.remove(os.path.dirname(os.path.realpath(__file__))+'/networkinfo.sh')

    # Get results
    result = dict()
    result = get_network_info()

    # Write network results to cache
    cachedir = '%s/cache' % os.path.dirname(os.path.realpath(__file__))
    output_plist = os.path.join(cachedir, 'networkinfo.plist')
    try:
        plistlib.writePlist(result, output_plist)
    except:
        with open(output_plist, 'wb') as fp:
            plistlib.dump(result, fp, fmt=plistlib.FMT_XML)
#    print plistlib.writePlistToString(result)

if __name__ == "__main__":
    main()
