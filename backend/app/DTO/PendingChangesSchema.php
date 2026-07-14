<?php
namespace App\DTO;

/**
 * Formal contract for the pending_changes JSON stored on TutorProfile.
 *
 * This class documents the exact shape of every key so the backend and
 * frontend both have a single authoritative reference instead of relying
 * on tribal knowledge scattered across controllers and Vue files.
 *
 * Usage — write:
 *   PendingChangesSchema::bio($profile->pending_changes, $bioValue)
 *
 * Usage — read:
 *   $schema = PendingChangesSchema::from($profile->pending_changes);
 *   $bio = $schema->bio;
 *
 * The class is intentionally lightweight: it does NOT validate values
 * (Laravel's Request validators handle that), it only provides named
 * accessors and named mutators so "magic string" key access disappears.
 */
final class PendingChangesSchema
{
    // ── Top-level fields ──────────────────────────────────────────────────────
    public readonly ?string $bio;
    public readonly ?string $status;
    public readonly ?string $name;
    public readonly ?string $submittedAt;

    /** @var array{path:string,url:string}|null */
    public readonly ?array $avatar;

    // ── Sections ──────────────────────────────────────────────────────────────
    /** @var array{subject_ids?:int[],location_ids?:int[],district_id?:int,days?:array,...}|null */
    public readonly ?array $preferences;

    /** @var array{gender?:string,date_of_birth?:string,religion?:string,...}|null */
    public readonly ?array $personalInfo;

    /** @var array{name?:string,relation?:string,phone?:string,address?:string}|null */
    public readonly ?array $emergencyContact;

    /** @var array{changes:array<string,array{action:string,id:?int,data:array}>}|null */
    public readonly ?array $education;

    /** @var array{upsert?:array<string,array>,delete?:int[]}|null */
    public readonly ?array $documents;

    // ── Known section keys ────────────────────────────────────────────────────
    public const SECTIONS = [
        'bio', 'status', 'name', 'preferences', 'personal_info',
        'emergency_contact', 'education', 'documents',
    ];

    private function __construct(array $raw)
    {
        $this->bio              = $raw['bio']               ?? null;
        $this->status           = $raw['status']            ?? null;
        $this->name              = $raw['name']              ?? null;
        $this->submittedAt      = $raw['submitted_at']      ?? null;
        $this->avatar           = isset($raw['avatar']['url']) ? $raw['avatar'] : null;
        $this->preferences      = $raw['preferences']       ?? null;
        $this->personalInfo     = $raw['personal_info']     ?? null;
        $this->emergencyContact = $raw['emergency_contact'] ?? null;
        $this->education        = $raw['education']         ?? null;
        $this->documents        = $raw['documents']         ?? null;
    }

    public static function from(?array $raw): self
    {
        return new self($raw ?? []);
    }

    /** Returns which sections are present (for rejection summaries, display, etc.) */
    public function presentSections(): array
    {
        return array_values(array_filter(self::SECTIONS, fn($s) =>
            ($s === 'bio'               && $this->bio               !== null) ||
            ($s === 'status'            && $this->status            !== null) ||
            ($s === 'name'              && $this->name              !== null) ||
            ($s === 'preferences'       && $this->preferences       !== null) ||
            ($s === 'personal_info'     && $this->personalInfo      !== null) ||
            ($s === 'emergency_contact' && $this->emergencyContact  !== null) ||
            ($s === 'education'         && $this->education         !== null) ||
            ($s === 'documents'         && $this->documents         !== null)
        ));
    }

    public function isEmpty(): bool
    {
        return $this->bio === null
            && $this->status === null
            && $this->name === null
            && $this->avatar === null
            && $this->preferences === null
            && $this->personalInfo === null
            && $this->emergencyContact === null
            && $this->education === null
            && $this->documents === null;
    }

    public function toArray(): array
    {
        return array_filter([
            'bio'               => $this->bio,
            'status'            => $this->status,
            'name'              => $this->name,
            'submitted_at'      => $this->submittedAt,
            'avatar'            => $this->avatar,
            'preferences'       => $this->preferences,
            'personal_info'     => $this->personalInfo,
            'emergency_contact' => $this->emergencyContact,
            'education'         => $this->education,
            'documents'         => $this->documents,
        ], fn($v) => $v !== null);
    }
}
