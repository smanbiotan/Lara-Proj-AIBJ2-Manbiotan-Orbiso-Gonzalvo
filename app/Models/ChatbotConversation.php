<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    protected $fillable = ['session_token', 'customer_id', 'status', 'escalated_at', 'last_activity_at'];

    protected function casts(): array
    {
        return ['escalated_at' => 'datetime', 'last_activity_at' => 'datetime'];
    }

    public function messages()
    {
        return $this->hasMany(ChatbotMessage::class);
    }
}
