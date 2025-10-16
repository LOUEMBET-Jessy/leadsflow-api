<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Interaction;
use App\Models\Lead;
use App\Models\User;

class InteractionSeeder extends Seeder
{
    public function run(): void
    {
        $leads = Lead::all();
        $users = User::all();

        $interactionTypes = ['Email', 'Appel', 'Reunion', 'Note', 'SMS'];
        $outcomes = ['positive', 'neutral', 'negative', 'follow_up_required'];

        foreach ($leads as $lead) {
            // Créer 2-5 interactions par lead
            $interactionCount = rand(2, 5);
            
            for ($i = 0; $i < $interactionCount; $i++) {
                $type = $interactionTypes[array_rand($interactionTypes)];
                $user = $users->random();
                $outcome = $outcomes[array_rand($outcomes)];
                
                $interaction = Interaction::create([
                    'lead_id' => $lead->id,
                    'user_id' => $user->id,
                    'type' => $type,
                    'subject' => $this->getSubjectForType($type, $lead),
                    'summary' => $this->getSummaryForType($type, $lead),
                    'details' => $this->getDetailsForType($type, $lead),
                    'date' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                    'duration' => $type === 'Appel' || $type === 'Reunion' ? rand(15, 120) : null,
                    'outcome' => $outcome,
                    'metadata' => $this->getMetadataForType($type),
                ]);

                // Mettre à jour la dernière interaction du lead
                if ($interaction->date > $lead->last_contact_at) {
                    $lead->update(['last_contact_at' => $interaction->date]);
                }
            }
        }
    }

    private function getSubjectForType($type, $lead)
    {
        return match($type) {
            'Email' => "Email de suivi - {$lead->name}",
            'Appel' => "Appel téléphonique - {$lead->company}",
            'Reunion' => "Réunion de présentation - {$lead->name}",
            'Note' => "Note de suivi - {$lead->name}",
            'SMS' => "SMS de rappel - {$lead->name}",
            default => "Interaction avec {$lead->name}"
        };
    }

    private function getSummaryForType($type, $lead)
    {
        return match($type) {
            'Email' => "Email envoyé pour présenter nos services de gestion de leads",
            'Appel' => "Appel effectué pour qualifier le besoin et présenter notre solution",
            'Reunion' => "Réunion de présentation de notre plateforme LeadFlow",
            'Note' => "Note de suivi sur l'avancement du prospect",
            'SMS' => "SMS de rappel pour la prochaine étape",
            default => "Interaction avec le prospect {$lead->name}"
        };
    }

    private function getDetailsForType($type, $lead)
    {
        return match($type) {
            'Email' => "Email détaillé présentant les fonctionnalités de LeadFlow, les avantages pour {$lead->company}, et les prochaines étapes. Le prospect semble intéressé par notre solution de gestion de leads.",
            'Appel' => "Appel téléphonique de 30 minutes avec {$lead->name}. Discussion sur les besoins de {$lead->company} en matière de gestion de leads. Le prospect a confirmé son intérêt et souhaite une démonstration.",
            'Reunion' => "Réunion de présentation d'une heure avec l'équipe de {$lead->company}. Démonstration complète de LeadFlow, questions-réponses, et discussion sur l'implémentation. Très bonne réception de la part de l'équipe.",
            'Note' => "Le prospect {$lead->name} de {$lead->company} progresse bien dans le processus. Budget confirmé, décision prévue dans 2 semaines. Prochaine étape : envoi de la proposition commerciale.",
            'SMS' => "SMS de rappel envoyé pour confirmer la réunion de demain. Le prospect a confirmé sa présence.",
            default => "Interaction avec {$lead->name} de {$lead->company}"
        };
    }

    private function getMetadataForType($type)
    {
        return match($type) {
            'Email' => [
                'template_used' => 'presentation_email',
                'opened' => true,
                'clicked' => true,
                'attachments' => ['presentation.pdf', 'brochure.pdf']
            ],
            'Appel' => [
                'call_duration' => rand(15, 120),
                'call_quality' => 'good',
                'recording_available' => false
            ],
            'Reunion' => [
                'meeting_type' => 'presentation',
                'attendees' => rand(2, 5),
                'presentation_used' => 'leadflow_demo.pptx',
                'follow_up_scheduled' => true
            ],
            'Note' => [
                'note_type' => 'follow_up',
                'priority' => 'medium',
                'tags' => ['important', 'follow_up']
            ],
            'SMS' => [
                'sms_provider' => 'twilio',
                'delivered' => true,
                'read' => true
            ],
            default => []
        };
    }
}