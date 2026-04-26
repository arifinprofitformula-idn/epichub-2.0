<?php

namespace App\Enums;

enum AccessLogAction: string
{
    case OrderPaidGrant = 'order_paid_grant';
    case BundleChildGrant = 'bundle_child_grant';
    case ManualGrant = 'manual_grant';
    case ManualRevoke = 'manual_revoke';
    case AccessViewed = 'access_viewed';
    case BundleAccessed = 'bundle_accessed';
    case FileViewed = 'file_viewed';
    case FileDownloaded = 'file_downloaded';
    case ExternalLinkOpened = 'external_link_opened';
    case CourseAccessed = 'course_accessed';
    case LessonViewed = 'lesson_viewed';
    case LessonCompleted = 'lesson_completed';
    case LessonAttachmentDownloaded = 'lesson_attachment_downloaded';
    case LessonAttachmentExternalOpened = 'lesson_attachment_external_opened';
    case EventRegistered = 'event_registered';
    case EventAccessed = 'event_accessed';
    case EventJoinClicked = 'event_join_clicked';
    case EventReplayOpened = 'event_replay_opened';
    case EventAttended = 'event_attended';
    case AccessDenied = 'access_denied';

    public function label(): string
    {
        return match ($this) {
            self::OrderPaidGrant => 'Grant dari order paid',
            self::BundleChildGrant => 'Grant child dari bundle',
            self::ManualGrant => 'Grant manual',
            self::ManualRevoke => 'Revoke manual',
            self::AccessViewed => 'Akses dibuka',
            self::BundleAccessed => 'Bundle dibuka',
            self::FileViewed => 'File dibuka',
            self::FileDownloaded => 'File diunduh',
            self::ExternalLinkOpened => 'External link dibuka',
            self::CourseAccessed => 'Kelas dibuka',
            self::LessonViewed => 'Lesson dibuka',
            self::LessonCompleted => 'Lesson selesai',
            self::LessonAttachmentDownloaded => 'Attachment lesson diunduh',
            self::LessonAttachmentExternalOpened => 'Link attachment lesson dibuka',
            self::EventRegistered => 'Registrasi event',
            self::EventAccessed => 'Event dibuka',
            self::EventJoinClicked => 'Klik join event',
            self::EventReplayOpened => 'Replay event dibuka',
            self::EventAttended => 'Event hadir',
            self::AccessDenied => 'Akses ditolak',
        };
    }
}

