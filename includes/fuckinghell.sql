SELECT event_participant_billing.id FROM event_participant_billing LEFT JOIN event_participant on event_participant_billing.event_participant_id = event_participant.id LEFT JOIN event on event_participant.event_id = event.id WHERE event.class_id = 15;
