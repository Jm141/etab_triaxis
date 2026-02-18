<?php
/**
 * Web Routes
 */

// Home
$router->get('', 'Home@index');
$router->get('/', 'Home@index');

// Authentication
$router->get('login', 'Auth@login');
$router->post('login', 'Auth@login');
$router->get('logout', 'Auth@logout');

// Dashboard
$router->get('dashboard', 'Dashboard@index');

// Judge Connection Management (Admin)
$router->get('judge-connections', 'JudgeConnections@index');

// Events
$router->get('events', 'Event@index');
$router->get('events/create', 'Event@create');
$router->post('events/store', 'Event@store');
// More specific routes must come BEFORE less specific ones
$router->post('events/{id}/generate-organizer-key', 'Event@generateOrganizerKey');
$router->post('events/{id}/regenerate-organizer-key', 'Event@regenerateOrganizerKey');
$router->get('events/{id}/edit', 'Event@edit');
$router->post('events/{id}/update', 'Event@update');
$router->post('events/{id}/delete', 'Event@delete');
$router->get('events/{id}', 'Event@show');

// Event Assignments
$router->get('events/{eventId}/assignments', 'EventAssignment@index');
$router->post('events/{eventId}/assignments/assign', 'EventAssignment@assign');
$router->post('events/{eventId}/assignments/unassign', 'EventAssignment@unassign');
$router->post('events/{id}/select', 'Event@select');

// Event Levels
$router->get('events/{eventId}/levels', 'EventLevel@index');
$router->post('events/{eventId}/levels/store', 'EventLevel@store');
$router->post('events/{eventId}/levels/{id}/update', 'EventLevel@update');
$router->post('events/{eventId}/levels/{id}/delete', 'EventLevel@delete');
$router->get('events/{eventId}/levels/{id}/edit', 'EventLevel@edit');     
$router->post('events/{eventId}/levels/{id}/edit', 'EventLevel@update');

// Rounds
$router->get('levels/{levelId}/rounds', 'Round@index');
$router->get('levels/{levelId}/rounds/{id}/edit', 'Round@edit');
$router->post('levels/{levelId}/rounds/store', 'Round@store');
$router->post('levels/{levelId}/rounds/{id}/update', 'Round@update');
$router->post('levels/{levelId}/rounds/{id}/delete', 'Round@delete');

// Criteria (Event-specific - kept for backward compatibility)
$router->get('events/{eventId}/criteria', 'Criteria@index');
$router->post('events/{eventId}/criteria/store', 'Criteria@store');
$router->post('events/{eventId}/criteria/{id}/update', 'Criteria@update');
$router->post('events/{eventId}/criteria/{id}/delete', 'Criteria@delete');

// Criteria Management (User-friendly global interface)
$router->get('criteria-management', 'CriteriaManagement@index');
$router->get('criteria-management/create', 'CriteriaManagement@create');
$router->post('criteria-management/store', 'CriteriaManagement@store');
$router->get('criteria-management/edit/{id}', 'CriteriaManagement@edit');
$router->post('criteria-management/update/{id}', 'CriteriaManagement@update');
$router->post('criteria-management/delete/{id}', 'CriteriaManagement@delete');

// Criteria Weights
$router->get('rounds/{roundId}/weights', 'CriteriaWeight@index');
$router->post('rounds/{roundId}/weights/store', 'CriteriaWeight@store');
$router->post('rounds/{roundId}/weights/{id}/update', 'CriteriaWeight@update');
$router->post('rounds/{roundId}/weights/{id}/delete', 'CriteriaWeight@delete');

// Judges (Event-specific)
$router->get('events/{eventId}/judges', 'Judge@index');
$router->post('events/{eventId}/judges/store', 'Judge@store');
$router->post('events/{eventId}/judges/{id}/delete', 'Judge@delete');
$router->post('judges/{id}/assign-round', 'Judge@assignRound');

// Judge Management (Global)
$router->get('judge-management', 'JudgeManagement@index');
$router->get('judge-management/create', 'JudgeManagement@create');
$router->post('judge-management/store', 'JudgeManagement@store');
$router->get('judge-management/{id}/edit', 'JudgeManagement@edit');
$router->post('judge-management/{id}/update', 'JudgeManagement@update');
$router->get('judge-management/{id}/assign-rounds', 'JudgeManagement@assignRounds');
$router->post('judge-management/{id}/update-round-assignments', 'JudgeManagement@updateRoundAssignments');
$router->post('judge-management/assign-to-event', 'JudgeManagement@assignToEvent');
$router->post('judge-management/{id}/remove-from-event', 'JudgeManagement@removeFromEvent');
$router->post('judge-management/import', 'JudgeManagement@import');
$router->get('judge-management/download-template', 'JudgeManagement@downloadTemplate');

// Contestants
$router->get('events/{eventId}/contestants', 'Contestant@index');
$router->post('events/{eventId}/contestants/store', 'Contestant@store');
$router->get('events/{eventId}/contestants/{id}/edit', 'Contestant@edit');
$router->post('events/{eventId}/contestants/{id}/update', 'Contestant@update');
$router->post('events/{eventId}/contestants/{id}/delete', 'Contestant@delete');
$router->post('events/{eventId}/contestants/import', 'Contestant@import');
$router->get('contestants/download-template', 'Contestant@downloadTemplate');

// Judge Scoring Interface
$router->get('judge/rounds', 'JudgeScoring@rounds');
// More specific route first (table view)
$router->get('judge/rounds/{roundId}/table', 'JudgeScoring@roundTable');
// Less specific route last (redirects to table)
$router->get('judge/rounds/{roundId}', 'JudgeScoring@round');
$router->get('judge/rounds/{roundId}/contestants/{contestantId}', 'JudgeScoring@score');
$router->post('judge/rounds/{roundId}/contestants/{contestantId}/submit', 'JudgeScoring@submit');
$router->post('judge/rounds/{roundId}/contestants/{contestantId}/auto-save', 'JudgeScoring@autoSave');
$router->post('judge/rounds/{roundId}/submit-all', 'JudgeScoring@submitAll');
$router->post('judge/rounds/{roundId}/request-edit-permission', 'JudgeScoring@requestEditPermission');

// Results
$router->get('events/{eventId}/results', 'Result@index');
$router->get('events/{eventId}/results/round/{roundId}', 'Result@round');
$router->get('events/{eventId}/results/round/{roundId}/summary', 'Result@summary');
$router->get('events/{eventId}/overwrite-keys', 'Result@overwriteKeys');
$router->post('events/{eventId}/results/calculate', 'Result@calculate');
$router->post('events/{eventId}/results/release', 'Result@release');

// Display/Scoreboard
$router->get('display/event/{eventId}', 'Display@event');
$router->get('display/round/{roundId}', 'Display@round');
$router->get('display/lineup', 'Display@lineup');
$router->get('display/lineup/{eventId}', 'Display@lineup');

// User Management
$router->get('user-management', 'UserManagement@index');
$router->get('user-management/create', 'UserManagement@create');
$router->post('user-management/store', 'UserManagement@store');
$router->get('user-management/edit/{id}', 'UserManagement@edit');
$router->post('user-management/{id}/update', 'UserManagement@update');
$router->post('user-management/{id}/delete', 'UserManagement@delete');
$router->post('user-management/{id}/toggle-active', 'UserManagement@toggleActive');

// Score Management (Admin/Tabulator can edit judge scores with permission)
$router->get('score-management/round/{roundId}', 'ScoreManagement@index');
$router->get('score-management/round/{roundId}/{judgeId}', 'ScoreManagement@index');
$router->get('score-management/edit/{id}', 'ScoreManagement@edit');
$router->post('score-management/{id}/update', 'ScoreManagement@update');
$router->get('score-management/deduct/{id}', 'ScoreManagement@deduct');
$router->post('score-management/{id}/request-deduction', 'ScoreManagement@requestDeduction');
$router->post('score-management/{id}/respond-deduction', 'ScoreManagement@respondToDeductionRequest');
$router->post('score-management/{id}/apply-deduction', 'ScoreManagement@applyDeduction');
$router->post('score-management/{id}/toggle-permission', 'ScoreManagement@toggleEditPermission');
$router->post('score-management/{id}/request-edit-permission', 'ScoreManagement@requestEditPermission');
$router->post('score-management/{id}/respond-permission', 'ScoreManagement@respondToPermissionRequest');
$router->post('score-management/{id}/generate-overwrite-key', 'ScoreManagement@generateOverwriteKey');
$router->get('score-management/print/{eventId}', 'ScoreManagement@printScores');
$router->get('score-management/print-round/{roundId}', 'ScoreManagement@printRoundScores');
$router->get('score-management/print-round/{roundId}/{judgeId}', 'ScoreManagement@printRoundScores');

// Notifications
$router->get('notifications', 'Notification@index');
$router->post('notifications/{id}/respond', 'Notification@respond');
$router->post('notifications/{id}/mark-read', 'Notification@markRead');

// Reports (Simplified)
$router->get('events/{eventId}/reports', 'Reports@index');
$router->get('events/{eventId}/reports/round/{roundId}', 'Reports@roundReport');
$router->get('events/{eventId}/reports/level/{levelId}', 'Reports@levelReport');
$router->get('events/{eventId}/reports/judge/{judgeId}', 'Reports@judgeReport');
$router->get('events/{eventId}/reports/finals', 'Reports@finalsReport');
$router->get('events/{eventId}/reports/formula', 'Reports@formula');
$router->post('events/{eventId}/reports/formula/save', 'Reports@saveFormula');
$router->post('events/{eventId}/reports/formula/{id}/delete', 'Reports@deleteFormula');
$router->post('events/{eventId}/reports/round/{roundId}/deduct', 'Reports@applyRoundDeduction');
$router->post('events/{eventId}/reports/level/{levelId}/deduct', 'Reports@applyLevelDeduction');
$router->post('events/{eventId}/reports/finals/deduct', 'Reports@applyFinalDeduction');
$router->post('events/{eventId}/reports/deductions/{id}/respond', 'Reports@respondReportDeduction');

// API Routes
$router->get('api/rankings/round/{roundId}', 'Api@getRankings');
$router->get('api/ping-judges', 'Api@pingJudges');
$router->post('api/judge-heartbeat', 'Api@judgeHeartbeat');

