<?php

namespace Database\Seeders;

use App\Models\EducationalResource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EducationalResourceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->resources() as $resource) {
            EducationalResource::query()->updateOrCreate(
                ['slug' => Str::slug($resource['title'])],
                [
                    ...$resource,
                    'slug' => Str::slug($resource['title']),
                    'type' => $resource['type'] ?? 'article',
                    'reading_time' => $resource['reading_time'] ?? 4,
                    'is_published' => true,
                ]
            );
        }
    }

    private function resources(): array
    {
        return [
            [
                'title' => 'Comment faire un CV professionnel ?',
                'category' => 'Orientation',
                'type' => 'guide',
                'description' => 'Un guide concret pour construire un CV etudiant clair, lisible et adapte aux stages.',
                'content' => implode("\n\n", [
                    'Un CV professionnel presente votre parcours en une page claire. Il doit aider le recruteur a comprendre rapidement votre formation, vos competences et vos projets.',
                    'Structure conseillee : informations personnelles, titre du profil, formation, experiences, projets academiques, competences techniques, langues et centres d interet utiles.',
                    'Pour un etudiant, les projets, stages, travaux pratiques et responsabilites associatives peuvent remplacer une longue experience professionnelle.',
                    'Evitez les paragraphes longs, les couleurs trop fortes, les fautes et les informations inutiles. Relisez votre CV et adaptez-le a chaque offre.',
                ]),
                'external_url' => 'https://www.canva.com/fr_fr/creer/cv/',
                'source_name' => 'Canva - Createur de CV',
                'practical_tips' => [
                    'Gardez votre CV sur une page si possible.',
                    'Placez les informations les plus importantes en haut.',
                    'Utilisez des verbes d action pour decrire vos missions.',
                    'Adaptez les competences a chaque stage ou emploi.',
                ],
                'checklist' => [
                    'Informations personnelles',
                    'Formation',
                    'Experiences',
                    'Competences',
                    'Langues',
                    'Projets',
                    'Mise en page claire',
                ],
            ],
            [
                'title' => 'Comment organiser son rapport de stage ?',
                'category' => 'Organisation academique',
                'type' => 'article',
                'description' => 'Une structure simple pour rediger un rapport de stage complet et coherent.',
                'content' => implode("\n\n", [
                    'Un rapport de stage doit raconter une experience professionnelle de maniere logique : contexte, objectifs, methodes, realisation et bilan.',
                    'Commencez par une page de garde propre, puis ajoutez les remerciements, le sommaire et une introduction qui presente le stage.',
                    'La partie centrale decrit l entreprise, le cahier des charges, la conception, les outils utilises, la realisation et les resultats.',
                    'Terminez par une conclusion personnelle : ce que vous avez appris, les difficultes rencontrees et les perspectives.',
                ]),
                'external_url' => 'https://www.scribbr.fr/memoire/rapport-de-stage/',
                'source_name' => 'Scribbr - Rapport de stage',
                'practical_tips' => [
                    'Preparez le plan avant de rediger.',
                    'Ajoutez des captures ou schemas seulement s ils expliquent votre travail.',
                    'Gardez une numerotation claire des titres.',
                    'Relisez la coherence entre cahier des charges et realisation.',
                ],
                'checklist' => [
                    'Page de garde',
                    'Remerciements',
                    'Introduction',
                    'Presentation entreprise',
                    'Cahier des charges',
                    'Conception UML',
                    'Realisation',
                    'Conclusion',
                ],
            ],
            [
                'title' => 'Gerer le stress avant les examens',
                'category' => 'Stress et anxiete',
                'type' => 'conseil',
                'description' => 'Des techniques rapides pour reduire la pression avant et pendant les examens.',
                'content' => implode("\n\n", [
                    'Le stress avant un examen est frequent. Il devient plus gerable quand vous combinez respiration, organisation et sommeil.',
                    'Avant l examen, preparez une liste courte des points essentiels au lieu de relire tout le cours sans priorite.',
                    'Pendant une montee de stress, respirez lentement : inspirez quatre secondes, bloquez deux secondes, expirez six secondes.',
                    'La veille, evitez les revisions tres tardives. Un sommeil correct ameliore la concentration et la memoire.',
                ]),
                'external_url' => 'https://www.calm.com/blog/breathing-exercises-for-anxiety',
                'source_name' => 'Calm - Breathing exercises',
                'video_url' => 'https://www.youtube.com/embed/acUZdGd_3Dg',
                'embed_url' => 'https://www.youtube.com/embed/acUZdGd_3Dg',
                'practical_tips' => [
                    'Respirez lentement pendant trois minutes.',
                    'Preparez votre sac et vos documents la veille.',
                    'Faites des pauses courtes pendant les revisions.',
                    'Dormez suffisamment avant l examen.',
                ],
                'checklist' => [
                    'Planning de revision',
                    'Fiches essentielles',
                    'Temps de pause',
                    'Sommeil',
                    'Respiration',
                ],
            ],
            [
                'title' => 'Preparer une soutenance orale',
                'category' => 'Academique',
                'type' => 'guide',
                'description' => 'Une methode pour structurer ses slides, gerer le temps et parler clairement.',
                'content' => implode("\n\n", [
                    'Une bonne soutenance ne consiste pas a lire un rapport. Elle explique clairement le probleme, la methode et les resultats.',
                    'Structure conseillee : contexte, objectifs, methodologie, realisation, resultats, limites et perspectives.',
                    'Limitez le texte sur les slides. Utilisez des schemas, captures et mots cles pour guider votre discours.',
                    'Repetez avec chronometre au moins deux fois et preparez les questions probables.',
                ]),
                'external_url' => 'https://www.canva.com/fr_fr/presentations/modeles/soutenance/',
                'source_name' => 'Canva - Modeles de presentation',
                'practical_tips' => [
                    'Une idee principale par slide.',
                    'Respectez le temps annonce.',
                    'Preparez une phrase de transition entre les parties.',
                    'Anticipez les questions du jury.',
                ],
                'checklist' => [
                    'Introduction claire',
                    'Plan annonce',
                    'Slides lisibles',
                    'Demonstration preparee',
                    'Conclusion',
                    'Questions possibles',
                ],
            ],
            [
                'title' => 'Comment eviter la procrastination ?',
                'category' => 'Motivation',
                'type' => 'conseil',
                'description' => 'Des actions simples pour commencer une tache et garder un rythme realiste.',
                'content' => implode("\n\n", [
                    'La procrastination vient souvent d une tache trop grande ou mal definie. La premiere etape consiste a reduire la tache.',
                    'Transformez un objectif vague en action precise : au lieu de "faire le rapport", commencez par "ecrire le plan en dix lignes".',
                    'Utilisez une session courte de vingt-cinq minutes, puis une pause. Le but est de demarrer, pas de tout terminer en une fois.',
                    'Suivez vos petites victoires chaque jour pour rendre la progression visible.',
                ]),
                'external_url' => 'https://todoist.com/fr/productivity-methods/pomodoro-technique',
                'source_name' => 'Todoist - Methode Pomodoro',
                'practical_tips' => [
                    'Commencez par une tache de dix minutes.',
                    'Decoupez les grands objectifs en sous-taches.',
                    'Eloignez les distractions pendant une session.',
                    'Cochez les taches terminees pour voir vos progres.',
                ],
                'checklist' => [
                    'Tache decoupee',
                    'Session courte',
                    'Telephone eloigne',
                    'Pause planifiee',
                    'Suivi quotidien',
                ],
            ],
            ...$this->category('Stress et anxiete', [
                'Comment gerer le stress avant les examens ?',
                'Exercices de respiration en 3 minutes',
                'Conseils pour mieux dormir pendant les examens',
                'Comment calmer une crise de stress ?',
            ], 'exercice'),
            ...$this->category('Organisation academique', [
                'Comment organiser son rapport de stage ?',
                'Methode simple pour preparer une soutenance',
                'Comment gerer son temps pendant les revisions ?',
                'Plan de travail hebdomadaire pour avancer dans un projet',
            ], 'guide'),
            ...$this->category('Motivation', [
                'Comment reprendre confiance en soi ?',
                'Que faire quand on se sent bloque ?',
                'Comment eviter la procrastination ?',
                'Petites habitudes pour rester motive',
            ], 'conseil'),
            ...$this->category('Orientation', [
                'Comment choisir une filiere ?',
                'Comment preparer son projet professionnel ?',
                'Comment rediger un CV etudiant ?',
                'Comment preparer un entretien de stage ?',
            ], 'guide'),
            ...$this->category('Bien-etre', [
                'Prendre soin de son mental pendant les etudes',
                'Pourquoi demander de l aide est un acte de courage',
                'Habitudes simples pour ameliorer son equilibre',
                'Comment parler de ses difficultes a un conseiller',
            ], 'article'),
        ];
    }

    private function category(string $category, array $titles, string $type): array
    {
        return array_map(fn (string $title) => [
            'title' => $title,
            'category' => $category,
            'type' => $type,
            'description' => 'Une ressource courte et pratique pour accompagner les etudiants au quotidien.',
            'content' => $this->content($title),
            'reading_time' => 4,
        ], $titles);
    }

    private function content(string $title): string
    {
        return implode("\n\n", [
            $title,
            'Commencez par identifier clairement la situation qui vous pese, puis choisissez une petite action realiste a faire aujourd hui.',
            'Prenez quelques minutes pour respirer, ecrire vos idees et separer ce qui est urgent de ce qui peut attendre.',
            'Si la difficulte persiste, demandez un rendez-vous avec un conseiller afin de construire un plan adapte a votre situation.',
        ]);
    }
}
