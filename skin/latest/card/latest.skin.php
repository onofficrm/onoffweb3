<?php
if (!defined('_GNUBOARD_')) exit;

$list_count = (is_array($list) && $list) ? count($list) : 0;
?>
<div class="latest-card__body">
    <h3 class="latest-card-title">
        <a href="<?php echo get_pretty_url($bo_table); ?>"><?php echo $bo_subject; ?></a>
    </h3>
    <ul class="latest-card-list">
    <?php for ($i = 0; $i < $list_count; $i++) { ?>
        <li class="latest-card-item">
            <a href="<?php echo get_pretty_url($bo_table, $list[$i]['wr_id']); ?>" class="latest-card-link">
                <?php
                if ($list[$i]['icon_secret']) {
                    echo '<i class="fa fa-lock" aria-hidden="true"></i><span class="sound_only">비밀글</span> ';
                }
                if ($list[$i]['is_notice']) {
                    echo '<strong>'.$list[$i]['subject'].'</strong>';
                } else {
                    echo $list[$i]['subject'];
                }
                if ($list[$i]['icon_new']) {
                    echo ' <span class="latest-card-new">N</span>';
                }
                ?>
            </a>
            <span class="latest-card-date"><?php echo $list[$i]['datetime2']; ?></span>
        </li>
    <?php } ?>
    <?php if ($list_count == 0) { ?>
        <li class="latest-card-item latest-card-item--empty">게시물이 없습니다.</li>
    <?php } ?>
    </ul>
    <a href="<?php echo get_pretty_url($bo_table); ?>" class="latest-more">
        <span class="sound_only"><?php echo $bo_subject; ?></span>더보기
    </a>
</div>
