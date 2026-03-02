<?php
/**
 * Video Timeline - Clip model
 */
class VideoTimelineClip extends Model {
    protected $table = 'video_timeline_clips';

    public function getByTrackId($trackId) {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE track_id = ? ORDER BY sort_order ASC, start_time ASC",
            [$trackId]
        );
    }

    public function deleteByTrackId($trackId) {
        return $this->db->query("DELETE FROM `{$this->table}` WHERE track_id = ?", [$trackId]);
    }
}
