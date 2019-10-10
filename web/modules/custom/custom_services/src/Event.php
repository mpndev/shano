<?php

namespace Drupal\custom_services;


use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

class Event {

  public $event = NULL;
  public $tickets = [];

  public function load($id) {
    $this->event = Node::load($id);
    $this->tickets = Paragraph::load($this->event->get('field_tickets')->getValue()[0]['target_id']);
  }

  public function id() {
    return $this->event->id();
  }

  public function __get($field) {
    return $this->event->get($field)->value;
  }

  public function haveAvailableTickets() {
    return $this->tickets->get('field_quantity')->getString() > 0;
  }

}
