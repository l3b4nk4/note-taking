<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_STORAGE_FILE', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'new-project-note-taking-app.json');

function getDefaultStorageData()
{
    return [
        'meta' => [
            'last_user_id' => 0,
            'last_note_id' => 0,
        ],
        'users' => [],
        'notes' => [],
    ];
}

function normalizeStoredUser($user)
{
    $user = is_array($user) ? $user : [];

    return [
        'id' => (int) ($user['id'] ?? 0),
        'username' => trim((string) ($user['username'] ?? '')),
        'email' => trim((string) ($user['email'] ?? '')),
        'password' => (string) ($user['password'] ?? ''),
    ];
}

function normalizeStoredNote($note)
{
    $note = is_array($note) ? $note : [];

    return [
        'id' => (int) ($note['id'] ?? 0),
        'user_id' => (int) ($note['user_id'] ?? 0),
        'title' => trim((string) ($note['title'] ?? '')),
        'content' => trim((string) ($note['content'] ?? '')),
        'category' => trim((string) ($note['category'] ?? '')),
    ];
}

function normalizeStorageData($data)
{
    $defaults = getDefaultStorageData();

    if (!is_array($data)) {
        return $defaults;
    }

    $meta = isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [];

    return [
        'meta' => [
            'last_user_id' => (int) ($meta['last_user_id'] ?? 0),
            'last_note_id' => (int) ($meta['last_note_id'] ?? 0),
        ],
        'users' => array_values(array_map('normalizeStoredUser', isset($data['users']) && is_array($data['users']) ? $data['users'] : [])),
        'notes' => array_values(array_map('normalizeStoredNote', isset($data['notes']) && is_array($data['notes']) ? $data['notes'] : [])),
    ];
}

function ensureStorageFileExists()
{
    if (file_exists(APP_STORAGE_FILE)) {
        return;
    }

    file_put_contents(APP_STORAGE_FILE, json_encode(getDefaultStorageData(), JSON_PRETTY_PRINT));
    @chmod(APP_STORAGE_FILE, 0666);
}

function readStorageData()
{
    ensureStorageFileExists();

    $handle = fopen(APP_STORAGE_FILE, 'c+');
    if ($handle === false) {
        return getDefaultStorageData();
    }

    flock($handle, LOCK_SH);
    rewind($handle);
    $contents = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    $data = json_decode($contents ?: '', true);

    if (!is_array($data)) {
        $data = getDefaultStorageData();
        writeStorageData($data);
    }

    return normalizeStorageData($data);
}

function writeStorageData($data)
{
    $handle = fopen(APP_STORAGE_FILE, 'c+');
    if ($handle === false) {
        return false;
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        return false;
    }

    $normalized = normalizeStorageData($data);
    $payload = json_encode($normalized, JSON_PRETTY_PRINT);

    if ($payload === false) {
        $payload = json_encode(getDefaultStorageData(), JSON_PRETTY_PRINT);
    }

    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, $payload);
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    @chmod(APP_STORAGE_FILE, 0666);

    return true;
}

function nextStorageId(&$data, $key)
{
    $data['meta'][$key] = (int) ($data['meta'][$key] ?? 0) + 1;
    return $data['meta'][$key];
}

function getUserNotes($userId, $search = '', $category = '')
{
    $data = readStorageData();
    $search = trim((string) $search);
    $category = trim((string) $category);

    $notes = array_values(array_filter(array_map('normalizeStoredNote', $data['notes']), function ($note) use ($userId, $search, $category) {
        if ((int) ($note['user_id'] ?? 0) !== (int) $userId) {
            return false;
        }

        if ($search !== '') {
            $haystack = ($note['title'] ?? '') . "\n" . ($note['content'] ?? '');
            if (stripos($haystack, $search) === false) {
                return false;
            }
        }

        if ($category !== '' && ($note['category'] ?? '') !== $category) {
            return false;
        }

        return true;
    }));

    usort($notes, function ($left, $right) {
        return (int) ($right['id'] ?? 0) <=> (int) ($left['id'] ?? 0);
    });

    return $notes;
}

function getUserCategories($userId)
{
    $categories = [];

    foreach (getUserNotes($userId) as $note) {
        $category = trim((string) ($note['category'] ?? ''));
        if ($category !== '') {
            $categories[$category] = true;
        }
    }

    $result = array_keys($categories);
    sort($result, SORT_NATURAL | SORT_FLAG_CASE);

    return $result;
}

function findUserNoteById($userId, $noteId)
{
    foreach (readStorageData()['notes'] as $note) {
        if ((int) ($note['id'] ?? 0) === (int) $noteId && (int) ($note['user_id'] ?? 0) === (int) $userId) {
            return normalizeStoredNote($note);
        }
    }

    return null;
}

function createUserNote($userId, $noteData)
{
    $data = readStorageData();

    $data['notes'][] = [
        'id' => nextStorageId($data, 'last_note_id'),
        'user_id' => (int) $userId,
        'title' => trim((string) ($noteData['title'] ?? '')),
        'content' => trim((string) ($noteData['content'] ?? '')),
        'category' => trim((string) ($noteData['category'] ?? '')),
    ];

    return writeStorageData($data);
}

function updateUserNote($userId, $noteId, $noteData)
{
    $data = readStorageData();

    foreach ($data['notes'] as &$note) {
        if ((int) ($note['id'] ?? 0) !== (int) $noteId || (int) ($note['user_id'] ?? 0) !== (int) $userId) {
            continue;
        }

        $note['title'] = trim((string) ($noteData['title'] ?? ''));
        $note['content'] = trim((string) ($noteData['content'] ?? ''));
        $note['category'] = trim((string) ($noteData['category'] ?? ''));
        $note = normalizeStoredNote($note);

        return writeStorageData($data);
    }

    return false;
}

function deleteUserNote($userId, $noteId)
{
    $data = readStorageData();
    $originalCount = count($data['notes']);

    $data['notes'] = array_values(array_filter($data['notes'], function ($note) use ($userId, $noteId) {
        return !(
            (int) ($note['id'] ?? 0) === (int) $noteId &&
            (int) ($note['user_id'] ?? 0) === (int) $userId
        );
    }));

    if (count($data['notes']) === $originalCount) {
        return false;
    }

    return writeStorageData($data);
}
