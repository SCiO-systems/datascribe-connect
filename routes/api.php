<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\v1\AuthController;
use App\Http\Controllers\API\v1\UserController;
use App\Http\Controllers\API\v1\BlocksController;
use App\Http\Controllers\API\v1\UserAvatarController;
use App\Http\Controllers\API\v1\OAuth\Auth0Controller;
use App\Http\Controllers\API\v1\OAuth\ORCIDController;
use App\Http\Controllers\API\v1\UserPasswordController;
use App\Http\Controllers\API\v1\VocabulariesController;
use App\Http\Controllers\API\v1\QuestionnairesController;
use App\Http\Controllers\API\v1\Integrations\ScioController;
use App\Http\Controllers\API\v1\TermsController;

// API v1
Route::prefix('v1')->name('api.v1.')->group(
    function () {

        // --- OAUTH ROUTES ---
        Route::prefix('oauth')->group(
            function () {

                // ORCID.
                Route::prefix('orcid')->group(
                    function () {
                        Route::get('/', [ORCIDController::class, 'redirect']);
                        Route::get('/callback', [ORCIDController::class, 'callback']);
                    }
                );

                // Auth0.
                Route::prefix('auth0')->group(
                    function () {
                        Route::get('/', [Auth0Controller::class, 'redirect']);
                        Route::get('/callback', [Auth0Controller::class, 'callback']);
                    }
                );
            }
        );

        // Authenticate a user.
        Route::post('/login', [AuthController::class, 'login'])->name('login');

        // Logout a user.
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Register a new user.
        Route::post('/register', [UserController::class, 'store']);

        // Return the authenticated user or 401.
        Route::get('/auth/token/check', [AuthController::class, 'check']);

        // Authenticated and authorized (store) routes.
        Route::middleware(['auth.jwt'])->group(
            function () {

                // LANGUAGES.
                Route::get('/languages', [ScioController::class, 'getLanguages']);

                // ONTOLOGIES.
                Route::get('/ontologies', [ScioController::class, 'getOntologies']);

                // SEMANTIC SUGGESTIONS.
                Route::get('/semantic_suggestions', [ScioController::class, 'getSemanticSuggestions']);

                // UNITS.
                Route::get('/units', [ScioController::class, 'getUnits']);

                // AGRONOMIC DATES.
                Route::get('/agronomic_dates', [ScioController::class, 'getAgronomicDates']);

                // CROPS.
                Route::get('/crops', [ScioController::class, 'getCrops']);

                // EXTRACT KEYWORDS.
                Route::get('/extracted_keywords', [ScioController::class, 'getExtractedKeywords']);

                // QUESTIONS.
                Route::get('/questions', [ScioController::class, 'searchQuestions']);

                // PREVIEW LINK.
                Route::get('/preview-link/{questionnaire}', [ScioController::class, 'getPreviewLink']);

                // QUESTIONNAIRES.

                Route::post('questionnaires/import', [QuestionnairesController::class, 'import']);

                // Questionnaire management.
                Route::post(
                    'questionnaires/{questionnaire}/change_ownership', [
                    QuestionnairesController::class,
                    'changeOwnership'
                    ]
                )->name('questionnaires.change_ownership');

                Route::get(
                    'questionnaires/{questionnaire}/download', [
                    QuestionnairesController::class,
                    'download'
                    ]
                )->name('questionnaires.download');

                Route::get(
                    'questionnaires/{questionnaire}/download_carob_script', [
                    QuestionnairesController::class,
                    'downloadCarobScript'
                    ]
                )->name('questionnaires.download_carob_script');

                Route::post(
                    'questionnaires/{questionnaire}/clone', [
                    QuestionnairesController::class,
                    'clone'
                    ]
                );

                Route::apiResource('questionnaires', QuestionnairesController::class);

                // VOCABULARIES.

                Route::get('vocabularies/multiple', [VocabulariesController::class, 'getMultiple']);
                Route::post('vocabularies/search', [VocabulariesController::class, 'search']);
                Route::post('vocabularies/import', [VocabulariesController::class, 'import']);

                Route::post(
                    'vocabularies/{vocabulary}/publish', [
                    VocabulariesController::class,
                    'publish'
                    ]
                )->name('vocabularies.publish');

                Route::apiResource('vocabularies', VocabulariesController::class);

                // BLOCKS.

                Route::get('blocks/multiple', [BlocksController::class, 'getMultiple']);
                Route::post('blocks/search', [BlocksController::class, 'search']);
                Route::post('blocks/import', [BlocksController::class, 'import']);

                Route::post(
                    'blocks/{block}/publish', [
                    BlocksController::class,
                    'publish'
                    ]
                )->name('blocks.publish');

                Route::apiResource('blocks', BlocksController::class);

                // SEMANTIC TERMS.
                Route::post('terms', [TermsController::class, 'store']);
                Route::get('terms/categories', [TermsController::class, 'categories']);

                // USER.

                // User management.
                Route::apiResource('users', UserController::class)->only(['index', 'show', 'update']);

                // User avatar management.
                Route::post('/users/{user}/avatar', [UserAvatarController::class, 'update']);
                Route::delete('/users/{user}/avatar', [UserAvatarController::class, 'destroy']);

                // Update user password.
                Route::put('/users/{user}/password', [UserPasswordController::class, 'update']);
            }
        );
    }
);
