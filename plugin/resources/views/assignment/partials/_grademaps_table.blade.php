<div>
    @if (!$charon->grademaps->isEmpty())

        <h2 class="title">{{ translate('grades') }}</h2>

        <table class="table is-bordered">

            <thead>
            <tr>
                <th>{{ translate('grade_name') }}</th>
                <th>{{ translate('your_points') }}</th>
            </tr>
            </thead>

            <tfoot>
            <tr>
                <td><strong>{{ translate('total') }}:</strong></td>
                <td>
                    {{ $charon->userGrade ? floatval($charon->userGrade->finalgrade) : '0' }}p
                    /
                    {{ floatval($charon->maxGrade) }}p
                </td>
            </tr>
            </tfoot>
            <tbody>

            @foreach ($charon->grademaps as $grademap)
                <tr>
                    <td>{{ $grademap->name }}</td>
                    <td>
                        {{ $grademap->userGrade ? floatval($grademap->userGrade->finalgrade) : '0' }}p
                        /
                        {{ floatval($grademap->gradeItem->grademax) }}p
                    </td>
                </tr>
            @endforeach
            </tbody>

        </table>

    @endif
</div>
