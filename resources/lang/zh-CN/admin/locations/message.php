<?php

return array(

    'does_not_exist' => '位置不存在',
    'assoc_users'    => '此位置目前不可删除，因为它是至少是一个资产或用户记录的所在位置，或是有资产分配给它，或是另一个位置的上级位置。请更新您的记录以不再引用此位置，然后重试。 ',
    'assoc_assets'	 => '删除失败，该位置已与其它资产关联。请先更新资产以取消关联，然后重试。 ',
    'assoc_child_loc'	 => '删除失败，该位置是一个或多个子位置的上层节点。请更新地理位置信息以取消关联，然后重试。 ',
    'assigned_assets' => '已分配的资产',
    'current_location' => '当前地理位置',
    'open_map' => '在 :map_provider_icon 地图中打开',


    'create' => array(
        'error'   => '位置没有被创建，请重试。',
        'success' => '位置创建成功。'
    ),

    'update' => array(
        'error'   => '位置没有被更新，请重试。',
        'success' => '位置更新成功。'
    ),

    'restore' => array(
        'error'   => '位置未恢复，请重试',
        'success' => '位置恢复成功。'
    ),

    'delete' => array(
        'confirm'   	=> '确定删除这个位置吗?',
        'error'   => '删除位置的过成中出现了一点儿问题，请重试。',
        'success' => '位置已经成功删除。'
    )

);