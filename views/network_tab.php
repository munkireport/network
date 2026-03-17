<div id="network-tab"></div>

<div id="lister" style="font-size: large; float: right;">
    <a href="/show/listing/network/network" title="List">
        <i class="btn btn-default tab-btn fa fa-list"></i>
    </a>
</div>
<div id="report_btn" style="font-size: large; float: right;">
    <a href="/show/report/network/network_report" title="Report">
        <i class="btn btn-default tab-btn fa fa-th"></i>
    </a>
</div>
<h2 data-i18n="network.network"></h2>

<div id="network-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>

<script>
$(document).on('appReady', function(){
    $.getJSON(appUrl + '/module/network/get_tab_data/' + serialNumber, function(data){
        if (!data) {
            $('#network-msg').text(i18n.t('no_data'));
            return;
        }

        // Hide loading message and update badge
        $('#network-msg').text('');
        $('#network-cnt').text(data.length);

        const skipThese = ['service'];
        let clientDetail = '';

        // Helper functions
        const formatValue = (prop, value, d) => {
            if (!value || value === '' || value === null || value === 'none' || prop === '') return '';
            if (prop === 'ipv6prefixlen' && d.ipv6ip === 'none') return '';

            // Status handling
            if (prop === 'status') {
                return value === 1 ? 
                    '<span class="label label-success">' + i18n.t('connected') + '</span>' :
                    '<span class="label label-danger">' + i18n.t('disconnected') + '</span>';
            }

            // Translation mapping
            const translations = {
                'manual': 'network.manual',
                'Automatic': 'network.automatic',
                'autoselect': 'network.autoselect',
                'autoselect (half-duplex)': 'network.autoselecthalf',
                'autoselect (full-duplex)': 'network.autoselectfull',
                'not set': 'network.notset',
                'dhcp': 'DHCP',
                'bootp': 'BOOTP'
            };

            if (translations[value]) {
                return translations[value].startsWith('network.') ? 
                    i18n.t(translations[value]) : value.toUpperCase();
            }

            // Special formatting
            if (prop === 'wireless_card_type' && value.includes('spairport_wireless_card_type_wifi')) {
                return value.replace('spairport_wireless_card_type_wifi', 'Wi-Fi');
            }

            // Boolean values
            if (['overrideprimary', 'ipv6coverrideprimary', 'airdrop_supported', 'wow_supported'].includes(prop)) {
                return i18n.t(value === "1" ? 'yes' : 'no');
            }

            // Supported channels formatting - preserved exactly as original
            if (prop === 'supported_channels') {
                return value.replace(" 10 (2GHz)", "<br> 10 (2GHz)")
                           .replace(" 100 (5GHz)", "<br>100 (5GHz)")
                           .replace(" 128 (5GHz)", "<br>128 (5GHz)")
                           .replace(" 157 (5GHz)", "<br>157 (5GHz)")
                           .replace("36 (5GHz)", "<br><br>36 (5GHz)")
                           .replace(" 33 (6GHz)", "<br>33 (6GHz)")
                           .replace(" 65 (6GHz)", "<br>65 (6GHz)")
                           .replace(" 97 (6GHz)", "<br>97 (6GHz)")
                           .replace(" 125 (6GHz)", "<br>125 (6GHz)")
                           .replace(" 153 (6GHz)", "<br>153 (6GHz)")
                           .replace(" 181 (6GHz)", "<br>181 (6GHz)")
                           .replace(" 209 (6GHz)", "<br>209 (6GHz)")
                           .replace(" 1 (6GHz)", "<br><br>1 (6GHz)");
            }

            // DNS formatting
            if (prop === 'dhcp_domain_name_servers' || prop === 'ipv4dns') {
                return value.replace(/, /g, ',<br>');
            }

            return value;
        };

        const getNetworkIcon = service => {
            const iconMap = {
                'Wi-Fi': 'fa-wifi',
                'AirPort': 'fa-wifi',
                'Ethernet': 'fa-indent fa-rotate-270',
                'iPhone': 'fa-mobile',
                'phone': 'fa-mobile',
                'iPad': 'fa-tablet',
                'ablet': 'fa-tablet',
                'utun': 'fa-train',
                'Serial': 'fa-ellipsis-h',
                'vmnet': 'fa-clone',
                'bond': 'fa-pause',
                'Bluetooth': 'fa-bluetooth-b',
                'odem': 'fa-tty',
                'Thunderbolt': 'fa-bolt',
                'USB': 'fa-usb',
                'FireWire': 'fa-fire-extinguisher',
                'VPN': 'fa-building-o'
            };

            for (const [key, icon] of Object.entries(iconMap)) {
                if (service.includes(key)) return icon;
            }
            return 'fa-globe';
        };

        const updateClientDetail = (prop, value, d) => {
            if (d.status !== "1") return;
            if (!['ipv4ip', 'ipv4dns', 'ipv6ip', 'ipv6dns', 'ethernet', 'externalip'].includes(prop)) return;
            if (clientDetail.includes(prop)) return;

            const formattedValue = formatValue(prop, value, d);
            if (!formattedValue) return;

            clientDetail += prop;

            // Handle IP dependencies
            if ((prop === 'ipv4ip' && !d.ipv6ip) || 
                (prop === 'ipv6ip' && !d.ipv4ip) || 
                (prop === 'ipv4dns' && !d.ipv6ip) || 
                (prop === 'ipv6ip' && !d.ipv4dns)) {
                clientDetail += prop.startsWith('ipv4') ? 'ipv6ip' : 'ipv4ip';
            }

            // Update network icon for IP addresses
            if (prop === 'ipv4ip' || prop === 'ipv6ip') {
                const icon = getNetworkIcon(d.service);
                $('.fa.fa-sitemap, .fa.fa-fa-sitemap').addClass(icon);

                if (!clientDetail.includes('service')) {
                    clientDetail += 'service';
                    $('.machine-hostname').parent().parent().parent()
                        .append($('<tr>')
                            .append($('<th>').append(i18n.t('network.active_service')))
                            .append($('<td>').append(d.service)));
                }
            }

            // Add to client detail
            $('.machine-hostname').parent().parent().parent()
                .append($('<tr>')
                    .append($('<th>').append(i18n.t('network.' + prop)))
                    .append($('<td>').append(formattedValue)));
        };

        data.forEach(d => {
            let rows = '';
            
            // Generate rows from data
            Object.entries(d).forEach(([prop, value]) => {
                if (!skipThese.includes(prop)) {
                    const formattedValue = formatValue(prop, value, d);
                    if (formattedValue) {
                        rows += `<tr><th>${i18n.t('network.'+prop)}</th><td>${formattedValue}</td></tr>`;
                        updateClientDetail(prop, value, d);
                    }
                }
            });

            if (rows) {
                const icon = getNetworkIcon(d.service);
                const maxWidth = d.service.includes('Wi-Fi') || d.service.includes('AirPort') ? 850 : 550;
                
                $('#network-tab').append(
                    $('<div>')
                        .append($('<h4>')
                            .append($('<i>').addClass('fa ' + icon))
                            .append(' ' + d.service))
                        .append($('<div>')
                            .css('max-width', maxWidth + 'px')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>').html(rows))))
                );
            }
        });
    });
});
</script>
