<?php
/**
 * Created by PhpStorm.
 * @author domenico domenico@translated.net / ostico@gmail.com
 * Date: 14/04/17
 * Time: 21.42
 *
 */

namespace API\V2\Json;


use API\App\Json\OutsourceConfirmation;
use CatUtils;
use Chunks_ChunkStruct;
use Langs_Languages;
use LQA\ChunkReviewDao;
use ManageUtils;
use Routes;
use TmKeyManagement_ClientTmKeyStruct;
use Users_UserStruct;
use Utils;
use WordCount_Struct;

class Job {

    /**
     * @var \Users_UserStruct
     */
    protected $user;

    /**
     * @var bool
     */
    protected $called_from_api = false;

    /**
     * @var TmKeyManagement_ClientTmKeyStruct[]
     */
    protected $keyList = [];

    /**
     * @param \Users_UserStruct $user
     *
     * @return $this
     */
    public function setUser( Users_UserStruct $user = null ) {
        $this->user = $user;
        return $this;
    }

    /**
     * @param bool $called_from_api
     *
     * @return $this
     */
    public function setCalledFromApi( $called_from_api ) {
        $this->called_from_api = (bool)$called_from_api;
        return $this;
    }

    /**
     * @param Chunks_ChunkStruct $jStruct
     *
     * @return array
     */
    protected function getKeyList( Chunks_ChunkStruct $jStruct ) {

        if( empty( $this->user ) ){
            return [];
        }

        if ( !$this->called_from_api ) {
            $out = $jStruct->getClientKeys( $this->user, \TmKeyManagement_Filter::OWNER )[ 'job_keys' ];
        } else {
            $out = $jStruct->getClientKeys( $this->user, \TmKeyManagement_Filter::ROLE_TRANSLATOR )[ 'job_keys' ];
        }

        return ( new JobClientKeys( $out ) )->render();

    }

    /**
     * @param $jStruct Chunks_ChunkStruct
     *
     * @return array
     * @throws \Exception
     * @throws \Exceptions\NotFoundError
     */
    public function renderItem( Chunks_ChunkStruct $jStruct ) {

        $outsourceInfo = $jStruct->getOutsource();
        $tStruct       = $jStruct->getTranslator();
        $outsource     = null;
        $translator    = null;
        if ( !empty( $outsourceInfo ) ) {
            $outsource = ( new OutsourceConfirmation( $outsourceInfo ) )->render();
        } else {
            $translator = ( !empty( $tStruct ) ? ( new JobTranslator() )->renderItem( $tStruct ) : null );
        }

        $jobStats = new WordCount_Struct();
        $jobStats->setIdJob( $jStruct->id );
        $jobStats->setDraftWords( $jStruct->draft_words + $jStruct->new_words ); // (draft_words + new_words) AS DRAFT
        $jobStats->setRejectedWords( $jStruct->rejected_words );
        $jobStats->setTranslatedWords( $jStruct->translated_words );
        $jobStats->setApprovedWords( $jStruct->approved_words );

        $lang_handler = Langs_Languages::getInstance();

        $warningsCount = $jStruct->getWarningsCount();

        $project = $jStruct->getProject();

        $result = [
                'id'                    => (int)$jStruct->id,
                'password'              => $jStruct->password,
                'source'                => $jStruct->source,
                'target'                => $jStruct->target,
                'sourceTxt'             => $lang_handler->getLocalizedName( $jStruct->source ),
                'targetTxt'             => $lang_handler->getLocalizedName( $jStruct->target ),
                'status'                => $jStruct->status_owner,
                'subject'               => $jStruct->subject,
                'owner'                 => $jStruct->owner,
                'open_threads_count'    => (int)$jStruct->getOpenThreadsCount(),
                'create_timestamp'      => strtotime( $jStruct->create_date ),
                'created_at'            => Utils::api_timestamp( $jStruct->create_date ),
                'create_date'           => $jStruct->create_date,
                'formatted_create_date' => ManageUtils::formatJobDate( $jStruct->create_date ),
                'quality_overall'       => CatUtils::getQualityOverallFromJobStruct( $jStruct ),
                'pee'                   => $jStruct->getPeeForTranslatedSegments(),
                'private_tm_key'        => $this->getKeyList( $jStruct ),
                'warnings_count'        => $warningsCount->warnings_count,
                'warning_segments'      => ( isset( $warningsCount->warning_segments ) ? $warningsCount->warning_segments : [] ),
                'stats'                 => CatUtils::getFastStatsForJob( $jobStats, false ),
                'outsource'             => $outsource,
                'translator'            => $translator,
                'total_raw_wc'          => (int)$jStruct->total_raw_wc,
                'urls'                  => [
                        'translate' => Routes::translate(
                                $project->name,
                                $jStruct->id,
                                $jStruct->password,
                                $jStruct->source,
                                $jStruct->target
                        )
                ],
                'quality_summary'       => [
                        'equivalent_class' => $jStruct->getQualityInfo(),
                        'quality_overall'  => $jStruct->getQualityOverall(),
                        'errors_count'     => (int)$jStruct->getErrorsCount()
                ]
        ];

        if ( !$project->isFeatureEnabled( \Features::REVIEW_IMPROVED ) ) {

            $reviewChunk = ChunkReviewDao::findOneChunkReviewByIdJobAndPassword(
                    $jStruct->id, $jStruct->password
            );

            $result[ 'urls' ][ 'revise' ] = Routes::revise(
                    $project->name,
                    $jStruct->id,
                    ( !empty( $reviewChunk ) ? $reviewChunk->review_password : $jStruct->password ),
                    $jStruct->source,
                    $jStruct->target
            );

        }

        return $result;

    }

}