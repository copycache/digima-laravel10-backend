<table>
    <thead>
        <tr>
            <th style="background-color: #0fb200; color: #ffffff; font-weight: bold; text-align: center; vertical-align: center;">Question</th>
            <th style="background-color: #0fb200; color: #ffffff; font-weight: bold; text-align: center; vertical-align: center;">Choices</th>
            <th style="background-color: #0fb200; color: #ffffff; font-weight: bold; text-align: center; vertical-align: center;">Total Answered</th>
            <th style="background-color: #0fb200; color: #ffffff; font-weight: bold; text-align: center; vertical-align: center;">Percentage Answered</th>
        </tr>
    </thead>
    <tbody>
        @foreach($questions as $question)
            <tr>
                <td>{{ $question->survey_question }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @foreach($question->choices as $choice)
                <tr>
                    <td></td>
                    <td>{{ $choice->survey_choices_details }}</td>
                    <td>{{ $choice->count }}</td>
                    <td align="right">{{ App\Http\Controllers\Admin\AdminExportController::get_percentage($choice->count, $question->total_count) }}%</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
