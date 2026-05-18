<div id="network-tab"></div>
<h2 data-i18n="network.network"></h2>

<div id="network-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>

<script>
$(document).on('appReady', function(){
    $.getJSON(appUrl + '/module/network/get_tab_data/' + serialNumber, function(data){
        if( ! data ){
            // Change loading message to no data
            $('#network-msg').text(i18n.t('no_data'));

        } else {

            // Hide loading/no data message
            $('#network-msg').text('');

            // Update the tab badge count
            $('#network-cnt').text(data.length);
            var skipThese = ['service'];
            var clientDetail = "";
            $.each(data, function(i,d){

                // Generate rows from data
                var rows = ''
                for (var prop in d){
                    // Skip skipThese
                    if(skipThese.indexOf(prop) == -1){
                        if ((d[prop] == '' || d[prop] == null || d[prop] == "none" || prop == '') && d[prop] !== 0){
                           // Do nothing for empty values to blank them
                        } else if(prop == 'ipv6prefixlen' && d['ipv6ip'] == 'none'){
                           // Do nothing for IPv6 prefix length when ipv6ip is none
                        } else if(prop == 'status' && d[prop] == 1){
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td><span class="label label-success">'+i18n.t('connected')+'</span></td></tr>';
                        } else if(prop == 'status' && d[prop] == 0){
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td><span class="label label-danger">'+i18n.t('disconnected')+'</span></td></tr>';
                        } else if(d[prop] == "manual"){
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+i18n.t('network.manual')+'</td></tr>';
                        } else if(d[prop] == "Automatic"){
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+i18n.t('network.automatic')+'</td></tr>';
                        } else if(d[prop] == "autoselect"){
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+i18n.t('network.autoselect')+'</td></tr>';
                        } else if(d[prop] == "autoselect (half-duplex)"){
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+i18n.t('network.autoselecthalf')+'</td></tr>';
                        } else if(d[prop] == "autoselect (full-duplex)"){
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+i18n.t('network.autoselectfull')+'</td></tr>';
                        } else if(d[prop] == "not set"){
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+i18n.t('network.notset')+'</td></tr>';
                        } else if(d[prop] == "dhcp"){
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>DHCP</td></tr>';
                        } else if(d[prop] == "bootp"){
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>BOOTP</td></tr>';
                        } else if(prop == "wireless_card_type" && d[prop].includes("spairport_wireless_card_type_wifi")){
                           // Apple Silicon Macs report this differently
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+d[prop].replace("spairport_wireless_card_type_wifi", "Wi-Fi")+'</td></tr>';
                            
                        // Boolean Values
                        } else if((prop == 'overrideprimary' || prop == 'ipv6coverrideprimary' || prop == 'airdrop_supported' || prop == 'wow_supported')&& d[prop] == "1"){
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+i18n.t('yes')+'</td></tr>';
                        } else if((prop == 'overrideprimary' || prop == 'ipv6coverrideprimary' || prop == 'airdrop_supported' || prop == 'wow_supported')&& d[prop] == "0"){
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+i18n.t('no')+'</td></tr>';

                        // // Format supported channels
                        } else if (prop == 'supported_channels'){
                           d[prop] = d[prop].replace(" 10 (2GHz)", "<br> 10 (2GHz)").replace(" 100 (5GHz)", "<br>100 (5GHz)").replace(" 128 (5GHz)", "<br>128 (5GHz)").replace(" 157 (5GHz)", "<br>157 (5GHz)").replace("36 (5GHz)", "<br><br>36 (5GHz)").replace(" 33 (6GHz)", "<br>33 (6GHz)").replace(" 65 (6GHz)", "<br>65 (6GHz)").replace(" 97 (6GHz)", "<br>97 (6GHz)").replace(" 125 (6GHz)", "<br>125 (6GHz)").replace(" 153 (6GHz)", "<br>153 (6GHz)").replace(" 181 (6GHz)", "<br>181 (6GHz)").replace(" 209 (6GHz)", "<br>209 (6GHz)").replace(" 1 (6GHz)", "<br><br>1 (6GHz)")
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+d[prop]+'</td></tr>';

                        // Format DHCP DNS
                        } else if (prop == 'dhcp_domain_name_servers'){
                           d[prop] = d[prop].replace(", ", ",<br>")
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+d[prop]+'</td></tr>';

                        // Append to the client detail, only if it is an active network service and we've not already appeneded the data
                        } else if(d["status"] == "1" && ((prop == "ipv4ip" || prop == "ipv4dns" || prop == "ipv6ip" || prop == "ipv6dns" || prop == "ethernet"|| prop == "externalip") && ! clientDetail.includes(prop))){

                           // Format DNS lines if more than one DNS server
                           if (prop == "ipv4dns"){
                               d[prop] = d[prop].replace(", ", ",<br>")
                           }

                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+d[prop]+'</td></tr>';

                           clientDetail = clientDetail + prop

                           // Only show IP address and DNS for first active service
                           if (prop == "ipv4ip" && d["ipv6ip"] == null){
                               clientDetail = clientDetail + "ipv6ip"
                           }
                           if (prop == "ipv6ip" && d["ipv4ip"] == null){
                               clientDetail = clientDetail + "ipv4ip"
                           }
                           if (prop == "ipv4dns" && d["ipv6ip"] == null){
                               clientDetail = clientDetail + "ipv6ip"
                           }
                           if (prop == "ipv6ip" && d["ipv4dns"] == null){
                               clientDetail = clientDetail + "ipv4dns"
                           }

                           // Update the network icon on the client detail
                           if (prop == "ipv4ip" || prop == "ipv6ip"){

                               if (d["service"].includes("Wi-Fi") || d["service"].includes("AirPort")){
                                   $('.fa.fa-sitemap')
                                       .addClass("fa fa-wifi")
                                   $('.fa.fa-fa-sitemap') // This is the broken reportdata client detail
                                       .addClass("fa fa-wifi")
                               } else if (d["service"].includes("Ethernet")){
                                   $('.fa.fa-sitemap')
                                       .addClass("fa fa-indent fa-rotate-270")
                                   $('.fa.fa-fa-sitemap') // This is the broken reportdata client detail
                                       .addClass("ffa fa-indent fa-rotate-270")
                               } else if (d["service"].includes("iPhone") || d["service"].includes("phone")){
                                   $('.fa.fa-sitemap')
                                       .addClass("fa fa-mobile")
                                   $('.fa.fa-fa-sitemap') // This is the broken reportdata client detail
                                       .addClass("fa fa-mobile")
                               } else{
                                   $('.fa.fa-fa-sitemap') // This is the broken reportdata client detail
                                       .addClass("fa fa-sitemap") // We're going to fix it by setting the default here
                               }

                               // Only include first active service
                               if (! clientDetail.includes("service")){
                                   clientDetail = clientDetail + "service"

                                   // Add the active network service to the client detail
                                   $('.machine-hostname').parent().parent().parent()
                                   .append($('<tr>')
                                       .append($('<th>')
                                           .append(i18n.t('network.active_service')))
                                       .append($('<td>')
                                           .append(d["service"])))
                               }
                           }

                           $('.machine-hostname').parent().parent().parent()
                           .append($('<tr>')
                               .append($('<th>')
                                   .append(i18n.t('network.'+prop)))
                               .append($('<td>')
                                   .append(d[prop])))
                            
                        } else {
                           rows = rows + '<tr><th>'+i18n.t('network.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                        }
                    }
                }

                // Generate table
                if (d.service.includes("Wi-Fi") || d.service.includes("AirPort")){
                    $('#network-tab')
                        .append($('<h4>')
                        .append($('<a href="#tab_wifi-tab">')
                            .append($('<i>')
                                .addClass('fa fa-wifi'))
                            .append(' '+d.service)))
                        .append($('<div style="max-width:850px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("Ethernet")){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-indent fa-rotate-270'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("iPhone") || d.service.includes("phone")){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-mobile'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("iPad") || d.service.includes("ablet")){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-tablet'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("utun")){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-train'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("Serial")){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-ellipsis-h'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("vmnet")){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-clone'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("bond")){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-pause'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("Bluetooth")){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-bluetooth-b'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("odem")){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-tty'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("Thunderbolt")){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-bolt'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("USB")){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-usb'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("FireWire")){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-fire-extinguisher'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else if (d.service.includes("VPN") || ("vpnservername" in d && d.vpnservername !== null)){
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-building-o'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else {
                    $('#network-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-globe'))
                            .append(' '+d.service))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                }
            })
        }
    });
});
</script>
