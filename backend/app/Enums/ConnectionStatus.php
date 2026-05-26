<?php
namespace App\Enums;
enum ConnectionStatus: string {
    case Pending = 'pending';
    case AdminReviewing = 'admin_reviewing';
    case TutorContacted = 'tutor_contacted';
    case Connected = 'connected';
    case Declined = 'declined';
    case Closed = 'closed';
}
