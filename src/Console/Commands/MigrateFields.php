<?php

namespace KindWork\TwoFa\Console\Commands;

use Statamic\Auth\File\User;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateFields extends Command {
  use RunsInPlease;
  
  protected $name = '2fa:migrate:fields';
  protected $description = 'Migrate fields from 2FA V1 to to 2FA V2 format for File based users';
  protected $signature = '2fa:migrate:fields';

  protected $errors = [];
  
  protected $success = 0;

  public function handle() {
    try {
        $users = User::all();
    } catch (TypeError $e) {
        $this->error("Make sure your 'users' Stache store is configured in config/statamic/stache.php");
        return 1;
    }
    
    $total = $users->count();
    
    $this->line("Found {$total} user records to migrate...");
    $bar = false;
    
    if ($this->getOutput()->getVerbosity() <= OutputInterface::VERBOSITY_VERBOSE) {
        $bar = $this->output->createProgressBar($total);
    
        $bar->start();
    }
    
    $users->each(function (User $user) use ($bar) {
      try {
        $user->set(
          'TwoFa',
          $user->get('2FA')
        );
        $user->remove('2FA');
        $user->save();
        
        $user->setMeta(
          'two_fa_rember_token',
          $user->getMeta('2fa_rember_token')
        );
        $user->setMeta('2fa_rember_token', null);
        
        $user->setMeta(
          'two_fa_rember_token_expires_at',
          $user->getMeta('2fa_rember_token_expires_at')
        );
        $user->setMeta('2fa_rember_token_expires_at', null);
        
        $this->success++;
      } catch (TypeError $e) {
        array_push($this->errors, $e);
      }
      
      if ($bar) {
          $bar->advance();
      }
    });
    
    if ($bar) {
        $bar->finish();
        $this->newLine();
    }
    
    $this->newLine();
    $this->info("Successfully migrated {$this->success} out of {$total} users.");
    
    if (! empty($this->errors)) {
        $count = count($this->errors);
    
        $this->newLine();
    
        $this->warn("However, there were {$count} errors!");
    
        if ($this->getOutput()->isVerbose()) {
            $table = collect($this->errors)
                ->flatMap(fn ($errors, $index) => collect($errors)->transform(fn ($error) => [$index, Str::limit($error)]));
    
            $this->table(['ID', 'Errors'], $table->all());
    
            $this->newLine();
        } else {
            $this->line("Use the `-v` option to see more details or check your log.");
        }
    
        return 1;
    }
    
    return 0;
  }
}
