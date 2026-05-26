<?php

namespace App\Services\Ai;

use App\Enums\EmotionEnum;
use App\Enums\ProblemTypeEnum;
use App\Enums\SentimentEnum;
use App\Enums\UrgencyLevelEnum;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class AiProviderService
{
    public function analyze(string $text): array
    {
        $provider = (string) config('services.ai.provider', 'groq');
        $apiKey = (string) config('services.ai.api_key');

        if (! in_array($provider, ['openai', 'groq'], true) || $apiKey === '') {
            return $this->fallbackAnalysis($text);
        }

        try {
            return $this->providerCompatibleAnalysis($text, $apiKey, $provider);
        } catch (\Throwable) {
            return $this->fallbackAnalysis($text);
        }
    }

    public function chat(string $message): string
    {
        $provider = (string) config('services.ai.provider', 'groq');
        $apiKey = (string) config('services.ai.api_key');

        if (! in_array($provider, ['openai', 'groq'], true) || $apiKey === '') {
            return $this->fallbackChatResponse($message);
        }

        try {
            $defaultBaseUrl = $provider === 'groq'
                ? 'https://api.groq.com/openai/v1'
                : 'https://api.openai.com/v1';

            $response = Http::timeout(20)
                ->withToken($apiKey)
                ->post(rtrim((string) config('services.ai.base_url', $defaultBaseUrl), '/').'/chat/completions', [
                    'model' => (string) config('services.ai.model', 'llama-3.1-8b-instant'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Tu es l assistant intelligent de la Cellule d Ecoute. Reponds en francais simple, calme et utile. Oriente vers les rendez-vous, la messagerie, les evenements et le contact humain avec un conseiller si la situation est sensible.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],
                ]);

            if ($response->failed()) {
                throw new RuntimeException('AI provider request failed.');
            }

            $content = data_get($response->json(), 'choices.0.message.content');

            if (! is_string($content) || trim($content) === '') {
                return $this->fallbackChatResponse($message);
            }

            return trim($content);
        } catch (\Throwable) {
            return $this->fallbackChatResponse($message);
        }
    }

    private function providerCompatibleAnalysis(string $text, string $apiKey, string $provider): array
    {
        $defaultBaseUrl = $provider === 'groq'
            ? 'https://api.groq.com/openai/v1'
            : 'https://api.openai.com/v1';

        $response = Http::timeout(20)
            ->withToken($apiKey)
            ->post(rtrim((string) config('services.ai.base_url', $defaultBaseUrl), '/').'/chat/completions', [
                'model' => (string) config('services.ai.model', 'llama-3.1-8b-instant'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Tu analyses des messages et motifs de rendez-vous etudiants pour aider un conseiller humain. Retourne uniquement un objet JSON avec les cles title, emotional_state, main_emotion, sentiment, urgency_level, problem_type, summary, key_signals, possible_causes, recommendation, suggested_response, suggested_action, risk_level_explanation, confidence_score. key_signals et possible_causes sont des tableaux de courtes chaines. Utilise seulement les valeurs suivantes: main_emotion = calme|stress|anxiete|tristesse|colere|confusion|fatigue|demotivation|peur|surcharge|isolement|blocage_academique ; sentiment = positive|neutral|negative|mixed ; urgency_level = low|medium|high|critical ; problem_type = psychique|physique|academique|social|orientation|organisation|autre. La reponse doit etre professionnelle, concrete, utile au conseiller, sans formules generiques comme stable ou neutre sans explication.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $text,
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('AI provider request failed.');
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('AI provider returned an empty payload.');
        }

        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $this->normalizeAnalysis($decoded);
    }

    private function fallbackAnalysis(string $text): array
    {
        $normalizedText = Str::lower(Str::ascii($text));
        $criticalKeywords = ['suicide', 'mourir', 'disparaitre', 'me faire du mal', 'je n en peux plus', 'danger', 'violence', 'agression'];
        $anxietyKeywords = ['stress', 'stresse', 'panique', 'angoisse', 'peur', 'anxiete', 'pression', 'dormir', 'insomnie'];
        $sadnessKeywords = ['triste', 'pleurer', 'vide', 'seul', 'seule', 'isolement', 'abandonne', 'mal moral'];
        $angerKeywords = ['colere', 'enerve', 'enervee', 'injuste', 'frustre', 'frustration', 'rage'];
        $confusionKeywords = ['perdu', 'perdue', 'je ne sais pas', 'bloque', 'bloquee', 'confus', 'confuse'];
        $fatigueKeywords = ['fatigue', 'epuise', 'epuisee', 'surcharge', 'plus d energie'];
        $demotivationKeywords = ['demotive', 'demotivee', 'abandonner', 'plus envie', 'je n arrive plus'];
        $academicKeywords = ['rapport', 'devoir', 'examen', 'note', 'cours', 'soutenance', 'memoire', 'rattrapage', 'deadline'];
        $orientationKeywords = ['orientation', 'specialite', 'filiere', 'master', 'avenir'];
        $organizationKeywords = ['organisation', 'organiser', 'planning', 'retard', 'prioriser', 'methode', 'temps'];
        $physicalKeywords = ['douleur', 'malade', 'sante', 'migraine', 'mal au ventre'];
        $socialKeywords = ['famille', 'amis', 'relation', 'colocataire', 'harcelement', 'isolement', 'conflit'];
        $positiveKeywords = ['merci', 'mieux', 'soulage', 'apaise', 'calme', 'ca va mieux', 'je progresse'];

        $matches = [
            'critical' => $this->matchKeywords($normalizedText, $criticalKeywords),
            'academic' => $this->matchKeywords($normalizedText, $academicKeywords),
            'anxiety' => $this->matchKeywords($normalizedText, $anxietyKeywords),
            'sadness' => $this->matchKeywords($normalizedText, $sadnessKeywords),
            'anger' => $this->matchKeywords($normalizedText, $angerKeywords),
            'confusion' => $this->matchKeywords($normalizedText, $confusionKeywords),
            'fatigue' => $this->matchKeywords($normalizedText, $fatigueKeywords),
            'demotivation' => $this->matchKeywords($normalizedText, $demotivationKeywords),
            'orientation' => $this->matchKeywords($normalizedText, $orientationKeywords),
            'organisation' => $this->matchKeywords($normalizedText, $organizationKeywords),
            'physical' => $this->matchKeywords($normalizedText, $physicalKeywords),
            'social' => $this->matchKeywords($normalizedText, $socialKeywords),
            'positive' => $this->matchKeywords($normalizedText, $positiveKeywords),
        ];

        $title = 'Besoin a clarifier avec le conseiller';
        $emotionalState = 'Preoccupation a explorer';
        $mainEmotion = EmotionEnum::CONFUSION->value;
        $sentiment = SentimentEnum::NEGATIVE->value;
        $urgency = UrgencyLevelEnum::LOW->value;
        $problemType = ProblemTypeEnum::AUTRE->value;
        $summary = "Le message exprime un besoin reel, mais le contexte reste partiel. Un echange structure permettra d'identifier la priorite concrete.";
        $keySignals = ['Besoin exprime sans contexte complet', 'Situation a clarifier avec precision'];
        $possibleCauses = ['Contexte incomplet dans le message initial', 'Difficulte a formuler clairement la demande'];
        $recommendation = "Commencer par clarifier la situation, le contexte recent et l'impact actuel sur le quotidien ou le parcours etudiant.";
        $suggestedResponse = "Merci pour votre message. Pour vous aider utilement, j'aimerais comprendre ce qui vous preoccupe le plus aujourd'hui et ce qui rend la situation difficile en ce moment.";
        $suggestedAction = "Proposer un premier entretien de clarification.";
        $riskExplanation = "Aucun signal critique explicite n'est repere dans ce texte, mais un besoin d'accompagnement reste present.";

        if ($matches['critical'] !== []) {
            $title = 'Detresse psychique necessitant une priorisation immediate';
            $emotionalState = 'Detresse avec risque eleve';
            $mainEmotion = EmotionEnum::PEUR->value;
            $urgency = UrgencyLevelEnum::CRITICAL->value;
            $problemType = ProblemTypeEnum::PSYCHIQUE->value;
            $summary = "Le texte contient des formulations compatibles avec une situation de detresse importante. La priorite est d'etablir rapidement un contact humain et d'evaluer la securite immediate.";
            $keySignals = ['Detresse explicite', 'Possible risque auto-agressif', 'Besoin d intervention rapide'];
            $possibleCauses = ['Souffrance psychique aiguë', 'Sentiment d impasse', 'Isolement ou absence de relais immediat'];
            $recommendation = "Contacter l'etudiant sans delai, adopter une posture de verification concrete et mobiliser le protocole humain approprie.";
            $suggestedResponse = "Merci d'avoir ecrit. Votre message montre une souffrance importante. Je souhaite prendre contact rapidement avec vous pour verifier votre securite et organiser une aide humaine immediate.";
            $suggestedAction = "Declencher une prise de contact prioritaire avec evaluation humaine immediate.";
            $riskExplanation = "Le niveau critique est retenu car plusieurs expressions evoquent une possible mise en danger ou une perte de controle.";
        } elseif ($matches['academic'] !== [] && ($matches['anxiety'] !== [] || $matches['confusion'] !== [] || $matches['organisation'] !== [])) {
            $title = 'Blocage academique a accompagner';
            $emotionalState = $matches['organisation'] !== [] ? 'Anxiete avec desorganisation du travail' : 'Anxiete avec blocage academique';
            $mainEmotion = $matches['confusion'] !== [] ? EmotionEnum::BLOCAGE_ACADEMIQUE->value : EmotionEnum::ANXIETE->value;
            $urgency = $matches['anxiety'] !== [] ? UrgencyLevelEnum::MEDIUM->value : UrgencyLevelEnum::LOW->value;
            $problemType = $matches['organisation'] !== [] ? ProblemTypeEnum::ORGANISATION->value : ProblemTypeEnum::ACADEMIQUE->value;
            $summary = "L'etudiant decrit une difficulte academique combinee a un stress concret. Le besoin principal semble etre a la fois emotionnel et methodologique.";
            $keySignals = ['Charge academique importante', 'Besoin de methode', 'Risque de retard ou de blocage'];
            $possibleCauses = ['Manque de plan clair', 'Pression liee aux echeances', 'Difficulte a structurer le travail'];
            $recommendation = "Explorer le niveau de pression ressenti, clarifier l'echeance la plus proche et proposer un plan de travail realiste par etapes.";
            $suggestedResponse = "Merci pour votre message. On peut avancer pas a pas. Je vous propose d'identifier d'abord ce qui bloque le plus, puis de definir ensemble une premiere etape faisable.";
            $suggestedAction = "Proposer un rendez-vous d'accompagnement academique ou d'organisation du travail.";
            $riskExplanation = "L'urgence est moyenne car la situation demande un accompagnement rapide sans signal critique immediat.";
        } elseif ($matches['orientation'] !== []) {
            $title = 'Questionnement d orientation a clarifier';
            $emotionalState = 'Incertitude face au parcours';
            $mainEmotion = EmotionEnum::CONFUSION->value;
            $sentiment = SentimentEnum::MIXED->value;
            $urgency = UrgencyLevelEnum::MEDIUM->value;
            $problemType = ProblemTypeEnum::ORIENTATION->value;
            $summary = "Le message traduit une hesitation sur le parcours ou les choix d'avenir, avec un besoin de cadrage et de projection.";
            $keySignals = ['Hesitation sur le parcours', 'Besoin de projection'];
            $possibleCauses = ['Manque de visibilite sur les options', 'Pression de choix', 'Difficulte a se projeter'];
            $recommendation = "Explorer les options perçues, les contraintes ressenties et les criteres de choix qui comptent le plus pour l'etudiant.";
            $suggestedResponse = "Merci pour votre message. Nous pouvons reprendre ensemble vos options, ce qui vous attire et ce qui vous freine afin de clarifier un choix plus serein.";
            $suggestedAction = "Proposer un entretien d orientation avec clarification des options.";
            $riskExplanation = "L'urgence reste moyenne car l'incertitude peut freiner la prise de decision, sans signal de danger immediat.";
        } elseif ($matches['physical'] !== []) {
            $title = 'Souffrance physique influencant le quotidien';
            $emotionalState = 'Inconfort physique avec retentissement possible';
            $mainEmotion = $matches['fatigue'] !== [] ? EmotionEnum::FATIGUE->value : EmotionEnum::STRESS->value;
            $urgency = $matches['anxiety'] !== [] ? UrgencyLevelEnum::MEDIUM->value : UrgencyLevelEnum::LOW->value;
            $problemType = ProblemTypeEnum::PHYSIQUE->value;
            $summary = "Le texte evoque une plainte physique qui peut peser sur le fonctionnement quotidien, le sommeil ou les etudes.";
            $keySignals = ['Retentissement physique', 'Impact possible sur la concentration'];
            $possibleCauses = ['Fatigue ou douleur persistante', 'Sommeil altere', 'Charge combinee physique et academique'];
            $recommendation = "Verifier l'impact concret sur les etudes et la vie quotidienne, puis orienter vers une aide adaptee si besoin.";
            $suggestedResponse = "Merci pour votre message. J'aimerais comprendre comment cette difficulte physique impacte vos journees et vos etudes afin de vous orienter au mieux.";
            $suggestedAction = "Evaluer le retentissement et proposer une orientation adaptee si necessaire.";
            $riskExplanation = "Le niveau retenu reste modere tant qu'aucun signal de gravite immediate n'apparait dans le texte.";
        } elseif ($matches['sadness'] !== [] || $matches['social'] !== []) {
            $title = $matches['social'] !== [] ? 'Isolement social ressenti par l etudiant' : 'Tristesse necessitant un espace d ecoute';
            $emotionalState = $matches['social'] !== [] ? 'Tristesse avec isolement relationnel' : 'Tristesse persistante';
            $mainEmotion = $matches['social'] !== [] ? EmotionEnum::ISOLEMENT->value : EmotionEnum::TRISTESSE->value;
            $urgency = UrgencyLevelEnum::MEDIUM->value;
            $problemType = $matches['social'] !== [] ? ProblemTypeEnum::SOCIAL->value : ProblemTypeEnum::PSYCHIQUE->value;
            $summary = "Le message laisse apparaitre une souffrance emotionnelle, possiblement renforcee par un manque de soutien ou un retrait relationnel.";
            $keySignals = ['Besoin de soutien humain', 'Risque de repli'];
            $possibleCauses = ['Sentiment de solitude', 'Tensions relationnelles', 'Charge emotionnelle non verbalisee'];
            $recommendation = "Laisser une place importante a l'ecoute, verifier le soutien disponible autour de l'etudiant et identifier ce qui aggrave le repli.";
            $suggestedResponse = "Merci pour votre confiance. Ce que vous decrivez semble lourd a porter seul. Nous pouvons prendre un temps d'echange pour comprendre ce qui vous pese et voir comment vous soutenir.";
            $suggestedAction = "Proposer un entretien d ecoute et explorer le reseau de soutien.";
            $riskExplanation = "Le niveau de risque reste sous surveillance car l'isolement et la tristesse peuvent favoriser un retrait progressif.";
        } elseif ($matches['anger'] !== []) {
            $title = 'Frustration forte a canaliser';
            $emotionalState = 'Colere avec tension relationnelle possible';
            $mainEmotion = EmotionEnum::COLERE->value;
            $urgency = UrgencyLevelEnum::MEDIUM->value;
            $problemType = $matches['social'] !== [] ? ProblemTypeEnum::SOCIAL->value : ProblemTypeEnum::AUTRE->value;
            $summary = "Le discours traduit une frustration active. Le besoin prioritaire semble etre d'etre entendu avant de rechercher des solutions concretes.";
            $keySignals = ['Frustration importante', 'Besoin de recadrage'];
            $possibleCauses = ['Sentiment d injustice', 'Conflit non resolu', 'Accumulation de tension'];
            $recommendation = "Commencer par accueillir les faits et l'emotion, puis recentrer progressivement l'echange sur le besoin et la marge d'action.";
            $suggestedResponse = "Merci pour votre message. Je comprends qu'il y a beaucoup de frustration dans cette situation. Nous pouvons reprendre les faits ensemble pour voir ce qui peut etre traite concretement.";
            $suggestedAction = "Structurer l'entretien autour des faits, du declencheur principal et du besoin immediate.";
            $riskExplanation = "L'urgence est moyenne car l'intensite emotionnelle peut compliquer la relation d'aide si elle n'est pas rapidement canalisee.";
        } elseif ($matches['positive'] !== []) {
            $title = 'Amelioration ressentie ou stabilisation positive';
            $emotionalState = 'Apaisement progressif';
            $mainEmotion = EmotionEnum::CALME->value;
            $sentiment = SentimentEnum::POSITIVE->value;
            $problemType = $matches['academic'] !== [] ? ProblemTypeEnum::ACADEMIQUE->value : ProblemTypeEnum::AUTRE->value;
            $summary = "Le message contient des indices d'apaisement ou de progression. Le conseiller peut consolider ce qui aide deja l'etudiant.";
            $keySignals = ['Amelioration percue', 'Capacites de reprise'];
            $possibleCauses = ['Soutien recu', 'Mise en place d une strategie utile', 'Retour progressif du controle'];
            $recommendation = "Identifier les appuis utiles, valoriser les strategies qui fonctionnent et maintenir un suivi proportionne.";
            $suggestedResponse = "Merci pour votre retour. C'est utile de voir ce qui commence a aller mieux. Nous pouvons nous appuyer sur ces elements pour consolider la suite.";
            $suggestedAction = "Valoriser les progres et identifier les facteurs protecteurs.";
            $riskExplanation = "Le niveau faible est retenu car le texte n'indique pas de detresse actuelle et mentionne plutot une amelioration.";
        } elseif ($matches['demotivation'] !== [] || $matches['fatigue'] !== []) {
            $title = 'Surcharge et perte d elan a accompagner';
            $emotionalState = 'Fatigue avec baisse de mobilisation';
            $mainEmotion = $matches['fatigue'] !== [] ? EmotionEnum::SURCHARGE->value : EmotionEnum::DEMOTIVATION->value;
            $urgency = UrgencyLevelEnum::MEDIUM->value;
            $problemType = $matches['academic'] !== [] ? ProblemTypeEnum::ACADEMIQUE->value : ProblemTypeEnum::PSYCHIQUE->value;
            $summary = "Le texte fait ressortir une usure progressive. Le risque principal semble etre la perte de rythme, de motivation ou de capacite a tenir les obligations.";
            $keySignals = ['Fatigue importante', 'Baisse de mobilisation'];
            $possibleCauses = ['Accumulation d obligations', 'Sommeil ou recuperation insuffisants', 'Absence de priorisation'];
            $recommendation = "Explorer la charge actuelle, le sommeil, les echeances et definir une priorite unique a traiter d'abord.";
            $suggestedResponse = "Merci pour votre message. Vous semblez porter beaucoup de choses en meme temps. Nous pouvons reprendre ensemble ce qui vous epuise le plus et trouver une premiere priorite realiste.";
            $suggestedAction = "Proposer un rendez-vous de recentrage avec priorisation des urgences concretes.";
            $riskExplanation = "Le niveau moyen est retenu car la surcharge peut rapidement faire basculer vers le retrait ou l'echec si rien n'est ajuste.";
        }

        $matchCount = 0;
        foreach ($matches as $group) {
            $matchCount += count($group);
        }

        return $this->normalizeAnalysis([
            'title' => $title,
            'emotional_state' => $emotionalState,
            'main_emotion' => $mainEmotion,
            'sentiment' => $sentiment,
            'urgency_level' => $urgency,
            'problem_type' => $problemType,
            'summary' => $summary,
            'key_signals' => $keySignals,
            'possible_causes' => $possibleCauses,
            'recommendation' => $recommendation,
            'suggested_response' => $suggestedResponse,
            'suggested_action' => $suggestedAction,
            'risk_level_explanation' => $riskExplanation,
            'confidence_score' => min(0.96, round(0.62 + min(5, $matchCount) * 0.05, 2)),
        ]);
    }

    private function normalizeAnalysis(array $analysis): array
    {
        return [
            'title' => Str::limit((string) ($analysis['title'] ?? 'Analyse emotionnelle a preciser'), 255, ''),
            'emotional_state' => Str::limit((string) ($analysis['emotional_state'] ?? 'Etat a preciser'), 255, ''),
            'main_emotion' => $this->normalizeEnumValue(
                $analysis['main_emotion'] ?? $analysis['emotion'] ?? null,
                EmotionEnum::values(),
                EmotionEnum::CONFUSION->value,
            ),
            'sentiment' => $this->normalizeEnumValue($analysis['sentiment'] ?? null, SentimentEnum::values(), SentimentEnum::NEUTRAL->value),
            'urgency_level' => $this->normalizeEnumValue($analysis['urgency_level'] ?? null, UrgencyLevelEnum::values(), UrgencyLevelEnum::LOW->value),
            'problem_type' => $this->normalizeEnumValue($analysis['problem_type'] ?? null, ProblemTypeEnum::values(), ProblemTypeEnum::AUTRE->value),
            'summary' => Str::limit((string) ($analysis['summary'] ?? ''), 1500, ''),
            'key_signals' => $this->normalizeSignals($analysis['key_signals'] ?? [], 5),
            'possible_causes' => $this->normalizeSignals($analysis['possible_causes'] ?? [], 6),
            'recommendation' => Str::limit((string) ($analysis['recommendation'] ?? ''), 1200, ''),
            'suggested_response' => Str::limit((string) ($analysis['suggested_response'] ?? ''), 1200, ''),
            'suggested_action' => Str::limit((string) ($analysis['suggested_action'] ?? ''), 255, ''),
            'risk_level_explanation' => Str::limit((string) ($analysis['risk_level_explanation'] ?? ''), 1200, ''),
            'confidence_score' => max(0, min(1, round((float) ($analysis['confidence_score'] ?? 0.5), 2))),
            'emotion' => $this->normalizeEnumValue(
                $analysis['main_emotion'] ?? $analysis['emotion'] ?? null,
                EmotionEnum::values(),
                EmotionEnum::CONFUSION->value,
            ),
        ];
    }

    private function normalizeEnumValue(mixed $value, array $allowedValues, string $fallback): string
    {
        $normalizedValue = Str::lower(Str::ascii((string) $value));

        foreach ($allowedValues as $allowedValue) {
            if ($normalizedValue === Str::lower(Str::ascii($allowedValue))) {
                return $allowedValue;
            }
        }

        return $fallback;
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, Str::lower(Str::ascii($needle)))) {
                return true;
            }
        }

        return false;
    }

    private function normalizeSignals(mixed $signals, int $maxItems): array
    {
        if (! is_array($signals)) {
            return [];
        }

        return collect($signals)
            ->map(fn ($signal) => Str::limit(trim((string) $signal), 160, ''))
            ->filter()
            ->unique()
            ->take($maxItems)
            ->values()
            ->all();
    }

    private function fallbackChatResponse(string $message): string
    {
        $input = Str::lower(Str::ascii($message));

        if (str_contains($input, 'rendez') || str_contains($input, 'rdv')) {
            return "Vous pouvez prendre rendez-vous depuis la section Rendez-vous. Choisissez un conseiller, une date, une heure et decrivez simplement votre besoin.";
        }

        if (str_contains($input, 'message') || str_contains($input, 'chat')) {
            return "La section Messages permet d echanger confidentiellement avec votre conseiller. Vous pouvez y envoyer du texte, des images ou des documents si besoin.";
        }

        if (str_contains($input, 'evenement') || str_contains($input, 'atelier')) {
            return "Consultez la section Evenements pour voir les ateliers disponibles, vous inscrire ou suivre vos participations.";
        }

        if ($this->containsAny($input, ['stress', 'anx', 'triste', 'mal', 'peur'])) {
            return "Merci de l avoir exprime. Si la situation vous pese, le plus utile est de prendre rapidement rendez-vous avec un conseiller afin d obtenir un accompagnement humain et confidentiel.";
        }

        if (str_contains($input, 'feedback')) {
            return "Le feedback se complete apres un rendez-vous termine. Vous le trouverez dans la section Feedbacks.";
        }

        return "Je peux vous aider a trouver la bonne section de la plateforme: Rendez-vous, Messages, Evenements ou Feedbacks. Si votre situation est sensible, prenez rendez-vous avec un conseiller.";
    }

    private function matchKeywords(string $haystack, array $needles): array
    {
        return collect($needles)
            ->filter(fn ($needle) => str_contains($haystack, Str::lower(Str::ascii($needle))))
            ->map(fn ($needle) => (string) $needle)
            ->values()
            ->all();
    }
}
