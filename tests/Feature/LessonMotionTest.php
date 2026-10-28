<?php

namespace Tests\Feature;

use App\Livewire\Quiz;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\QuizNode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class LessonMotionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Lesson $lesson;

    protected function setUp(): void
    {
        parent::setUp();
        $course = Course::create(['title' => ['fr' => 'Cours motion'], 'slug' => 'cours-motion']);
        $this->lesson = $course->modules()->create(['title' => ['fr' => 'Module'], 'position' => 0])
            ->lessons()->create(['title' => ['fr' => 'Leçon animée'], 'content' => ['fr' => 'Texte de la leçon'], 'position' => 0]);
        $this->user = User::factory()->create();
        Enrollment::create(['user_id' => $this->user->id, 'course_id' => $course->id, 'paid' => true]);
    }

    public function test_scenes_render_first_for_enrolled_user_only(): void
    {
        $this->lesson->update(['scenes' => [
            ['illustration' => 'density', 'title' => ['fr' => 'Densité adultes'], 'caption' => ['fr' => 'Environ 4 sujets par m²'], 'points' => ['fr' => ['Zone utile']], 'duration' => 6],
            ['illustration' => null, 'icon' => 'fa-wind', 'title' => ['fr' => 'Ventilation'], 'caption' => ['fr' => 'Sans courant d\'air'], 'duration' => 5],
        ]]);

        $this->actingAs(User::factory()->create())->get(route('lesson', $this->lesson->id))->assertForbidden();

        $html = $this->actingAs($this->user)->get(route('lesson', $this->lesson->id))->assertOk()
            ->assertSee('data-explainer', false)
            ->assertSee('Densité adultes')->assertSee('Environ 4 sujets par m²')->assertSee('Zone utile')
            ->assertSee('fa-wind', false)
            ->assertSee(__('lesson.explainer.step', ['n' => 2, 'total' => 2]))
            ->getContent();
        $this->assertLessThan(strpos($html, 'Texte de la leçon'), strpos($html, 'data-explainer'), 'Animation must come before the text.');
    }

    public function test_motion_file_player_and_sandboxed_embeds(): void
    {
        $this->lesson->update(['motion_file' => 'motion/demo.json', 'video_url' => 'https://www.youtube.com/embed/xyz']);

        $this->actingAs($this->user)->get(route('lesson', $this->lesson->id))->assertOk()
            ->assertSee('data-lottie-player', false)
            ->assertSee(str_replace('/', '\/', asset('storage/motion/demo.json')), false)
            ->assertSee(__('lesson.player.loading'))
            ->assertSee(__('lesson.player.error'))
            ->assertSee('sandbox="allow-scripts allow-same-origin allow-presentation"', false)
            ->assertSee(__('lesson.outline_open'));
    }

    /** start --(right)--> q2 --> end ; start --(wrong)--> q3 --> end */
    private function quizGraph(): array
    {
        $start = $this->lesson->nodes()->create(['question' => ['fr' => 'Q1 ?'], 'explanation' => ['fr' => 'Parce que.'], 'is_start' => true]);
        $q2 = $this->lesson->nodes()->create(['question' => ['fr' => 'Q2 ?']]);
        $q3 = $this->lesson->nodes()->create(['question' => ['fr' => 'Q3 remédiation ?']]);
        $right = $start->options()->create(['label' => ['fr' => 'Bonne'], 'is_correct' => true, 'next_node_id' => $q2->id]);
        $wrong = $start->options()->create(['label' => ['fr' => 'Mauvaise'], 'is_correct' => false, 'next_node_id' => $q3->id]);
        $end2 = $q2->options()->create(['label' => ['fr' => 'Fin 2'], 'is_correct' => true]);
        $q3->options()->create(['label' => ['fr' => 'Fin 3'], 'is_correct' => true]);

        return compact('start', 'q2', 'q3', 'right', 'wrong', 'end2');
    }

    public function test_story_quiz_branches_goes_back_and_saves_progress(): void
    {
        ['start' => $start, 'q2' => $q2, 'q3' => $q3, 'right' => $right, 'wrong' => $wrong, 'end2' => $end2] = $this->quizGraph();

        $quiz = Livewire::actingAs($this->user)->test(Quiz::class, ['lessonId' => $this->lesson->id])
            ->assertSet('nodeId', $start->id)
            ->assertDontSee('is_correct')->assertDontSee('correctIds');

        $quiz->call('answer', $wrong->id)
            ->assertReturned(fn ($r) => $r['correct'] === false && $r['correctIds'] === [$right->id]
                && $r['explanation'] === 'Parce que.' && $r['node']['id'] === $q3->id
                && ! array_key_exists('is_correct', $r['node']['options'][0]))
            ->assertSet('nodeId', $q3->id);

        $quiz->call('back')
            ->assertReturned(fn ($r) => $r['node']['id'] === $start->id && $r['answered'] === 0 && $r['canBack'] === false)
            ->assertSet('nodeId', $start->id)->assertSet('path', []);

        $quiz->call('answer', $right->id)->assertSet('nodeId', $q2->id)
            ->call('answer', $end2->id)
            ->assertReturned(fn ($r) => $r['finished'] === true && $r['percent'] === 100 && $r['good'] === 2)
            ->assertSet('finished', true);

        $this->assertSame(100, (int) LessonProgress::where('user_id', $this->user->id)->where('lesson_id', $this->lesson->id)->value('score'));
    }

    public function test_story_quiz_rejects_foreign_options_and_locks_state(): void
    {
        ['start' => $start, 'end2' => $end2] = $this->quizGraph();
        $quiz = Livewire::actingAs($this->user)->test(Quiz::class, ['lessonId' => $this->lesson->id]);

        $quiz->call('answer', $end2->id)->assertReturned(['stale' => true])->assertSet('nodeId', $start->id);

        $this->expectException(CannotUpdateLockedPropertyException::class);
        $quiz->set('history', [$start->id]);
    }

    public function test_quiz_requires_enrollment(): void
    {
        $this->quizGraph();
        Livewire::actingAs(User::factory()->create())->test(Quiz::class, ['lessonId' => $this->lesson->id])->assertForbidden();
    }
}
