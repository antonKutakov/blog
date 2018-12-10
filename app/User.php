<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    const IS_ADMIN  = 1;
    const IS_NORMAL = 0;
    const IS_BANNED = 1;
    const IS_ACTIVE = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function posts(){
        return $this->hasMany(Post::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public static function add($fields){
        $user = new static;
        $user->fill($fields);
        $user->save();
        return $user;
    }

    public function edit($fields){
        $this->fill($fields);
        $this->save();
    }

    public function generatePassword($password){
        if ($password != null) {
            $this->password = bcrypt($password);
            $this->save();
        }
    }

    public function remove(){
        if($this->avatar != null){
            Storage::delete('uploads/'.$this->avatar);
        }
        $this->delete();
    }

    public function uploadAvatar($image){
        if($image == null){
            return;
        } 
        if($this->avatar != null){
            Storage::delete('uploads/'.$this->avatar);
        }
        $filename = str_random(10).'.'.$image->extension();
        $image->storeAs('uploads', $filename);
        $this->avatar = $filename;
        $this->save();
    }

    public function getImage(){
        if($this->avatar == null){
            return '/img/no-avatar.jpg';
        }
        return '/uploads/'.$this->avatar;
    }

    public function makeAdmin(){
        $this->is_admin = User::IS_ADMIN;
        $this->save();
    }

    public function makeNormal(){
        $this->is_admin = User::IS_NORMAL;
        $this->save();
    }

    public function toggleAdmin($value){
        if($value == null){
            return $this->makeNormal();
        }
        return $this->makeAdmin();
    }

    public function ban(){
        $this->status = User::IS_BANNED;
        $this->save();
    }

    public function unban(){
        $this->status = User::IS_ACTIVE;
        $this->save();
    }

    public function toggleBan($value){
        if($value == null){
            return $this->unban();
        }
        return $this->ban();
    }
}
