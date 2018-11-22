<?php

$vlan_ranges = [];
if (env('VLAN_LABELS', [])) {
    foreach (env('VLAN_LABELS', []) as $label) {
        if (env('VLAN_RANGES_' . $label, [])) {
            if (env('VLAN_NAME_' . $label)) {
                $name = env('VLAN_NAME_' . $label);
            }
            else{
                $name = $label;
            }
            $vlan_ranges[$name] = env('VLAN_RANGES_' . $label, []);
        }
    }
}

return ['ipv4routers' => $vlan_ranges];
