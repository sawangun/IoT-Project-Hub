<article class="bg-white border border-slate-200 rounded-2xl overflow-hidden flex flex-col">
  <div class="relative h-44 bg-slate-200">
    <img src="{{ $project['image'] }}" alt="{{ $project['title'] }}" class="w-full h-full object-cover">
    <span class="absolute top-3 right-3 mono text-[10px] bg-white/90 px-2 py-1 rounded-full">● {{ $project['locker'] }}</span>
    <span class="absolute bottom-3 left-3 mono text-[10px] text-white bg-black/50 px-2 py-1 rounded">{{ $project['lab'] }}</span>
  </div>
  <div class="p-5 flex-1 flex flex-col">
    <div class="text-xs text-slate-600"><span class="text-amber-500">★</span> <b>{{ number_format($project['rating'], 1) }}</b> ({{ $project['reviews'] }} Peminjam)</div>
    <h3 class="font-bold text-lg mt-2 leading-snug"><a href="{{ route('projects.show', $project['slug']) }}">{{ $project['title'] }}</a></h3>
    <p class="text-sm text-slate-600 mt-2">{{ $project['desc'] }}</p>
    <div class="flex flex-wrap gap-1.5 mt-3">
      @foreach ($project['tags'] as $t)<span class="mono text-[10px] bg-slate-100 text-slate-600 px-2 py-1 rounded">{{ $t }}</span>@endforeach
    </div>
    <form method="POST" action="{{ route('borrow.store') }}" class="mt-auto pt-4">
      @csrf
      <input type="hidden" name="project_slug" value="{{ $project['slug'] }}">
      <button class="text-xs font-semibold bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Masuk Untuk Pinjam ↗</button>
    </form>
  </div>
</article>
