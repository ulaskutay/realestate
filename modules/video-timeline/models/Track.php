<?php
/**
 * Video Timeline - Track model
 */
class VideoTimelineTrack extends Model {
    protected $table = 'video_timeline_tracks';

    public function getByTimelineId($timelineId) {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE timeline_id = ? ORDER BY sort_order ASC",
            [$timelineId]
        );
    }
}
