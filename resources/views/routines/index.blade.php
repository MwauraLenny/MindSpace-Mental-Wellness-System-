<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Community Feed
            </h2>
            <a href="{{ route('routines.create') }}"
                class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">
                + Share a routine
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($moodFilter && $view === 'community' && $moodScope === 'match')
                <div class="bg-indigo-50 border border-indigo-200 text-indigo-700 px-4 py-3 rounded text-sm">
                    Showing routines matched to your current mood:
                    {{ [1=>'😢',2=>'😟',3=>'😐',4=>'🙂',5=>'😄'][$moodFilter] }} (level {{ $moodFilter }})
                </div>
            @endif

            @if(in_array($view, ['community', 'recommendations'], true))
                <section class="bg-white shadow sm:rounded-lg p-5 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Personalized recommendations</h3>
                            <p class="text-sm text-gray-600">
                                Based on your latest mood: {{ $recommendations['latestMoodEmoji'] }} {{ $recommendations['latestMoodLabel'] }}
                            </p>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                            {{ $recommendations['similarUserCount'] }} similar users matched
                        </span>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-2">Recommended coping strategies</p>
                        <ul class="grid gap-2 text-sm text-gray-700">
                            @foreach($recommendations['copingStrategies'] as $strategy)
                                <li class="bg-gray-50 border border-gray-100 rounded px-3 py-2">{{ $strategy }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-gray-700 mb-2">Recommended routines for you</p>
                        <div class="space-y-2">
                            @forelse($recommendations['routines'] as $recommendedRoutine)
                                <div class="border border-gray-100 rounded p-3 bg-white">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">{{ $recommendedRoutine->display_title }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ $recommendedRoutine->is_anonymous ? 'Anonymous user' : $recommendedRoutine->user->name }}
                                                · mood level {{ $recommendedRoutine->mood_tag }}
                                                · {{ $recommendedRoutine->category?->name ?? 'Uncategorized' }}
                                            </p>
                                        </div>
                                        <span class="text-xs px-2 py-1 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $recommendedRoutine->recommendation_reason }}
                                        </span>
                                    </div>

                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        <form method="POST" action="{{ route('routines.upvote', $recommendedRoutine->id) }}">
                                            @csrf
                                            <button type="submit" class="text-xs px-3 py-1.5 rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200">
                                                ❤ Like
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('routines.save', $recommendedRoutine->id) }}">
                                            @csrf
                                            <button type="submit" class="text-xs px-3 py-1.5 rounded bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
                                                Save routine
                                            </button>
                                        </form>

                                        <a
                                            href="#routine-{{ $recommendedRoutine->id }}"
                                            class="text-xs px-3 py-1.5 rounded bg-gray-100 text-gray-700 hover:bg-gray-200"
                                            data-start-now="{{ $recommendedRoutine->id }}"
                                        >
                                            Start now
                                        </a>
                                    </div>

                                    <details class="mt-3">
                                        <summary class="cursor-pointer text-xs font-medium text-gray-600 hover:text-gray-800">
                                            Why this was recommended
                                        </summary>
                                        <div class="mt-2 text-xs text-gray-600 bg-gray-50 border border-gray-100 rounded px-3 py-2">
                                            <ul class="space-y-1">
                                                @if($recommendedRoutine->recommendation_is_mood_match)
                                                    <li>Matches your current mood pattern.</li>
                                                @endif
                                                @if($recommendedRoutine->recommendation_is_similar_user)
                                                    <li>Used by people who logged similar moods.</li>
                                                @endif
                                                @if($recommendedRoutine->recommendation_is_preferred_category)
                                                    <li>Aligns with categories you engage with most.</li>
                                                @endif
                                                <li>Community engagement score: {{ $recommendedRoutine->recommendation_engagement_score }}.</li>
                                            </ul>
                                        </div>
                                    </details>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Recommendations will appear as the community shares more routines.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-4 space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('routines.index', ['view' => 'community', 'category_id' => $selectedCategory ?: null]) }}"
                        class="px-3 py-1.5 rounded text-sm {{ $view === 'community' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                        Community
                    </a>
                    <a href="{{ route('routines.index', ['view' => 'saved', 'category_id' => $selectedCategory ?: null]) }}"
                        class="px-3 py-1.5 rounded text-sm {{ $view === 'saved' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                        Saved
                    </a>
                    <a href="{{ route('routines.index', ['view' => 'mine', 'category_id' => $selectedCategory ?: null]) }}"
                        class="px-3 py-1.5 rounded text-sm {{ $view === 'mine' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                        My Routines
                    </a>
                    <a href="{{ route('routines.recommendations', ['category_id' => $selectedCategory ?: null]) }}"
                        class="px-3 py-1.5 rounded text-sm {{ $view === 'recommendations' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                        Recommendations
                    </a>
                </div>

                <form method="GET" action="{{ route('community.feed') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3 items-end">
                    <input type="hidden" name="view" value="{{ $view }}">

                    <div class="xl:col-span-2 min-w-0">
                        <label for="q" class="text-sm font-medium text-gray-700">Search</label>
                        <input
                            id="q"
                            name="q"
                            type="text"
                            value="{{ $search }}"
                            class="mt-1 w-full rounded border-gray-300 text-sm"
                            placeholder="Title, details, or contributor"
                        >
                    </div>

                    @if(in_array($view, ['community', 'recommendations'], true))
                        <div class="min-w-0">
                            <label for="mood_scope" class="text-sm font-medium text-gray-700">Mood scope</label>
                            <select id="mood_scope" name="mood_scope" class="mt-1 w-full rounded border-gray-300 text-sm">
                                <option value="all" @selected($moodScope === 'all')>All shared routines</option>
                                <option value="match" @selected($moodScope === 'match')>Match my latest mood</option>
                            </select>
                        </div>
                    @endif

                    <div class="min-w-0">
                        <label for="category_id" class="text-sm font-medium text-gray-700">Category</label>
                        <select id="category_id" name="category_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) $selectedCategory === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-0">
                        <label for="mood_tag" class="text-sm font-medium text-gray-700">Mood level</label>
                        <select id="mood_tag" name="mood_tag" class="mt-1 w-full rounded border-gray-300 text-sm">
                            <option value="0">All moods</option>
                            @foreach([1=>'😢 1',2=>'😟 2',3=>'😐 3',4=>'🙂 4',5=>'😄 5'] as $moodValue => $moodLabel)
                                <option value="{{ $moodValue }}" @selected((int) $explicitMoodTag === (int) $moodValue)>
                                    {{ $moodLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-0">
                        <label for="sort" class="text-sm font-medium text-gray-700">Sort</label>
                        <select id="sort" name="sort" class="mt-1 w-full rounded border-gray-300 text-sm">
                            <option value="trending" @selected($sort === 'trending')>Trending first</option>
                            <option value="latest" @selected($sort === 'latest')>Latest first</option>
                        </select>
                    </div>

                    <div class="xl:justify-self-end flex gap-2">
                        <button type="submit" class="w-full xl:w-auto px-3 py-2 rounded text-sm bg-indigo-600 text-white hover:bg-indigo-700">
                            Search
                        </button>
                        <a
                            href="{{ route('community.feed', ['view' => $view]) }}"
                            class="w-full xl:w-auto px-3 py-2 rounded text-sm bg-gray-100 text-gray-700 hover:bg-gray-200 text-center"
                        >
                            Reset
                        </a>
                    </div>
                </form>

                <div class="bg-gray-50 border border-gray-100 rounded p-3">
                    <p class="text-sm font-semibold text-gray-700 mb-2">Trending routines</p>
                    <div class="space-y-1 text-sm">
                        @forelse($trendingRoutines as $trend)
                            <a href="#routine-{{ $trend->id }}" class="block text-indigo-700 hover:underline">
                                {{ $trend->display_title }}
                                <span class="text-xs text-gray-500">(👍 {{ $trend->likes_count }} · 💬 {{ $trend->comments_count }})</span>
                            </a>
                        @empty
                            <p class="text-gray-500">No trending items yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            @forelse($routines as $routine)
                <div class="bg-white shadow sm:rounded-lg p-5" id="routine-{{ $routine->id }}">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-lg font-semibold text-gray-800">{{ $routine->display_title }}</p>
                            <p class="text-sm font-medium text-gray-500">
                                {{ $routine->is_anonymous ? 'Anonymous user' : $routine->user->name }}
                                &middot; mood level {{ $routine->mood_tag }}
                                {{ [1=>'😢',2=>'😟',3=>'😐',4=>'🙂',5=>'😄'][$routine->mood_tag] }}
                            </p>
                            <p class="text-xs text-indigo-600 mt-1">
                                {{ $routine->category?->name ?? 'Uncategorized' }}
                            </p>
                            <p class="mt-2 text-gray-800">{{ $routine->body }}</p>
                        </div>
                        <span class="text-sm text-gray-400 ml-4">{{ $routine->likes_count }} ❤</span>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('routines.upvote', $routine->id) }}">
                            @csrf
                            <button type="submit"
                                class="text-sm px-3 py-1 rounded {{ isset($likedRoutineIds[$routine->id]) ? 'bg-red-100 text-red-700' : 'bg-indigo-100 text-indigo-700' }} hover:bg-indigo-200">
                                {{ isset($likedRoutineIds[$routine->id]) ? '❤ Liked' : '❤ Like' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('routines.save', $routine->id) }}">
                            @csrf
                            <button type="submit"
                                class="text-sm px-3 py-1 rounded {{ isset($savedRoutineIds[$routine->id]) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }} hover:bg-gray-200">
                                {{ isset($savedRoutineIds[$routine->id]) ? 'Bookmarked' : 'Bookmark' }}
                            </button>
                        </form>

                        @if((int) $routine->user_id !== (int) Auth::id())
                            @if(isset($followedUserIds[$routine->user_id]))
                                <form method="POST" action="{{ route('routines.unfollow', $routine->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm px-3 py-1 rounded bg-amber-100 text-amber-700 hover:bg-amber-200">
                                        Following ({{ $followerCountByRoutine[$routine->id] ?? 0 }})
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('routines.follow', $routine->id) }}">
                                    @csrf
                                    <button type="submit" class="text-sm px-3 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200">
                                        Follow contributor ({{ $followerCountByRoutine[$routine->id] ?? 0 }})
                                    </button>
                                </form>
                            @endif
                        @endif

                        @if($routine->user_id !== Auth::id())
                            <details class="text-sm">
                                <summary class="cursor-pointer px-3 py-1 rounded bg-amber-100 text-amber-700 hover:bg-amber-200">Report routine</summary>
                                <form method="POST" action="{{ route('reports.store') }}" class="mt-2 p-3 rounded border border-amber-200 bg-amber-50/40 space-y-2">
                                    @csrf
                                    <input type="hidden" name="reportable_type" value="routine">
                                    <input type="hidden" name="reportable_id" value="{{ $routine->id }}">

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Reason</label>
                                        <select name="reason" class="w-full rounded border-gray-300 text-sm" required>
                                            <option value="spam">Spam</option>
                                            <option value="harassment">Harassment</option>
                                            <option value="hate_speech">Hate speech</option>
                                            <option value="self_harm_risk">Self-harm risk</option>
                                            <option value="misinformation">Misinformation</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Details (optional)</label>
                                        <textarea name="details" rows="2" class="w-full rounded border-gray-300 text-sm" placeholder="Add context for moderators"></textarea>
                                    </div>

                                    <button type="submit" class="text-xs px-3 py-1.5 rounded bg-amber-600 text-white hover:bg-amber-700">
                                        Submit report
                                    </button>
                                </form>
                            </details>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @foreach($reactionMeta as $reaction => $meta)
                            <form method="POST" action="{{ route('routines.react', $routine->id) }}">
                                @csrf
                                <input type="hidden" name="reaction" value="{{ $reaction }}">
                                <button type="submit"
                                    class="text-xs px-2.5 py-1 rounded border {{ (optional($myReactions[$routine->id] ?? null)->reaction === $reaction) ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 bg-white text-gray-600' }}">
                                    {{ $meta['emoji'] }} {{ $meta['label'] }}
                                    <span class="ml-1 text-gray-500">{{ $reactionCountsByRoutine[$routine->id][$reaction] ?? 0 }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>

                    <div class="mt-2 text-xs text-gray-500">
                        {{ $routine->saves_count }} saved · {{ $routine->comments_count }} comments · {{ $reactionCountsByRoutine[$routine->id]->sum() }} reactions · {{ $engagementCountsByRoutine[$routine->id] }} total engagement
                    </div>

                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <details>
                            <summary class="cursor-pointer text-sm font-semibold text-gray-700 hover:text-gray-900">
                                Show comments ({{ $routine->comments->count() }})
                            </summary>

                            <div class="mt-3">
                                <form method="POST" action="{{ route('routines.comments.store', $routine->id) }}" class="flex items-start gap-2 mb-3">
                                    @csrf
                                    <div class="flex-1">
                                        <textarea
                                            name="body"
                                            rows="2"
                                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                                            placeholder="Share encouragement or feedback..."
                                            required
                                        ></textarea>
                                        <label class="inline-flex items-center mt-2 text-xs text-gray-600">
                                            <input type="checkbox" name="is_anonymous" value="1" class="rounded border-gray-300 text-indigo-600 mr-2">
                                            Comment anonymously
                                        </label>
                                    </div>
                                    <button
                                        type="submit"
                                        class="text-sm bg-indigo-600 text-white px-3 py-2 rounded hover:bg-indigo-700"
                                    >
                                        Comment
                                    </button>
                                </form>

                                <div class="space-y-2">
                                    @forelse($routine->comments as $comment)
                                        <div class="bg-gray-50 border border-gray-100 rounded px-3 py-2" data-thread-root>
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs text-gray-500">
                                                {{ $comment->display_author }} · {{ optional($comment->created_at)->diffForHumans() }}
                                            </p>
                                            <p class="text-sm text-gray-700 mt-1">{{ $comment->body }}</p>
                                        </div>

                                        @if($comment->user_id === Auth::id())
                                            <form method="POST" action="{{ route('routines.comments.destroy', [$routine->id, $comment->id]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-700">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                                        <button
                                            type="button"
                                            class="text-indigo-600 hover:text-indigo-700"
                                            data-reply-toggle="{{ $comment->id }}"
                                        >
                                            Reply
                                        </button>

                                        @if($comment->user_id !== Auth::id())
                                            <details>
                                                <summary class="cursor-pointer text-amber-600 hover:text-amber-700">Report</summary>
                                                <form method="POST" action="{{ route('reports.store') }}" class="mt-2 p-3 rounded border border-amber-200 bg-amber-50/40 space-y-2 min-w-64">
                                                    @csrf
                                                    <input type="hidden" name="reportable_type" value="comment">
                                                    <input type="hidden" name="reportable_id" value="{{ $comment->id }}">
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Report reason</label>
                                                    <select name="reason" class="w-full rounded border-gray-300 text-xs" required>
                                                        <option value="spam">Spam</option>
                                                        <option value="harassment">Harassment</option>
                                                        <option value="hate_speech">Hate speech</option>
                                                        <option value="self_harm_risk">Self-harm risk</option>
                                                        <option value="misinformation">Misinformation</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">What happened?</label>
                                                    <textarea name="details" rows="2" class="w-full rounded border-gray-300 text-xs" placeholder="Add context for the moderation team"></textarea>
                                                    <button type="submit" class="w-full text-xs px-2.5 py-1.5 rounded bg-amber-600 text-white hover:bg-amber-700">Submit report</button>
                                                </form>
                                            </details>
                                        @endif

                                        @if($comment->replies->count() > 0)
                                            <button
                                                type="button"
                                                class="text-gray-500 hover:text-gray-700"
                                                data-thread-toggle="{{ $comment->id }}"
                                            >
                                                Hide replies ({{ $comment->replies->count() }})
                                            </button>
                                        @endif
                                    </div>

                                    <form
                                        method="POST"
                                        action="{{ route('routines.comments.store', $routine->id) }}"
                                        class="mt-2 hidden"
                                        data-reply-form="{{ $comment->id }}"
                                    >
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                        <div class="flex items-start gap-2">
                                            <div class="flex-1">
                                                <textarea
                                                    name="body"
                                                    rows="2"
                                                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                                                    placeholder="Write a reply..."
                                                    required
                                                ></textarea>
                                                <label class="inline-flex items-center mt-2 text-xs text-gray-600">
                                                    <input type="checkbox" name="is_anonymous" value="1" class="rounded border-gray-300 text-indigo-600 mr-2">
                                                    Reply anonymously
                                                </label>
                                            </div>
                                            <button
                                                type="submit"
                                                class="text-sm bg-indigo-600 text-white px-3 py-2 rounded hover:bg-indigo-700"
                                            >
                                                Reply
                                            </button>
                                        </div>
                                    </form>

                                    <div class="mt-2 space-y-2" data-thread-replies="{{ $comment->id }}">
                                        @foreach($comment->replies as $reply)
                                            <div class="ml-4 bg-white border border-gray-100 rounded px-3 py-2">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p class="text-xs text-gray-500">
                                                            {{ $reply->display_author }} · {{ optional($reply->created_at)->diffForHumans() }}
                                                        </p>
                                                        <p class="text-sm text-gray-700 mt-1">{{ $reply->body }}</p>
                                                    </div>

                                                    @if($reply->user_id === Auth::id())
                                                        <form method="POST" action="{{ route('routines.comments.destroy', [$routine->id, $reply->id]) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-xs text-red-600 hover:text-red-700">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>

                                                @if($reply->user_id !== Auth::id())
                                                    <details class="mt-2 text-xs">
                                                        <summary class="cursor-pointer text-amber-600 hover:text-amber-700">Report</summary>
                                                        <form method="POST" action="{{ route('reports.store') }}" class="mt-2 p-3 rounded border border-amber-200 bg-amber-50/40 space-y-2">
                                                            @csrf
                                                            <input type="hidden" name="reportable_type" value="comment">
                                                            <input type="hidden" name="reportable_id" value="{{ $reply->id }}">
                                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Report reason</label>
                                                            <select name="reason" class="w-full rounded border-gray-300 text-xs" required>
                                                                <option value="spam">Spam</option>
                                                                <option value="harassment">Harassment</option>
                                                                <option value="hate_speech">Hate speech</option>
                                                                <option value="self_harm_risk">Self-harm risk</option>
                                                                <option value="misinformation">Misinformation</option>
                                                                <option value="other">Other</option>
                                                            </select>
                                                            <label class="block text-xs font-semibold text-gray-700 mb-1">What happened?</label>
                                                            <textarea name="details" rows="2" class="w-full rounded border-gray-300 text-xs" placeholder="Add context for the moderation team"></textarea>
                                                            <button type="submit" class="w-full text-xs px-2.5 py-1.5 rounded bg-amber-600 text-white hover:bg-amber-700">Submit report</button>
                                                        </form>
                                                    </details>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-500">No comments yet. Be the first to react with words.</p>
                                    @endforelse
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow sm:rounded-lg p-6 text-center text-gray-500">
                    No routines found for the selected filters.
                    <a href="{{ route('routines.create') }}" class="text-indigo-600 underline ml-1">
                        Be the first to share!
                    </a>
                </div>
            @endforelse

        </div>
    </div>

    <script>
        document.querySelectorAll('[data-reply-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-reply-toggle');
                const form = document.querySelector(`[data-reply-form="${id}"]`);

                if (!form) {
                    return;
                }

                form.classList.toggle('hidden');
            });
        });

        document.querySelectorAll('[data-thread-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-thread-toggle');
                const replies = document.querySelector(`[data-thread-replies="${id}"]`);

                if (!replies) {
                    return;
                }

                replies.classList.toggle('hidden');

                const isHidden = replies.classList.contains('hidden');
                const countMatch = button.textContent.match(/\((\d+)\)/);
                const countLabel = countMatch ? countMatch[1] : '0';
                button.textContent = `${isHidden ? 'Show' : 'Hide'} replies (${countLabel})`;
            });
        });

        document.querySelectorAll('[data-start-now]').forEach((link) => {
            link.addEventListener('click', (event) => {
                const id = link.getAttribute('data-start-now');
                const target = document.getElementById(`routine-${id}`);

                if (!target) {
                    return;
                }

                event.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                target.classList.add('ring-2', 'ring-indigo-300');

                window.setTimeout(() => {
                    target.classList.remove('ring-2', 'ring-indigo-300');
                }, 1400);
            });
        });
    </script>
</x-app-layout>
